<?php

namespace Translate\Controller\Admin;

use Cake\Command\Command;
use Cake\Command\I18nExtractCommand;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOutput;
use Cake\Core\Configure;
use Cake\Http\Exception\NotFoundException;
use Cake\Utility\Inflector;
use Throwable;
use Translate\Controller\TranslateAppController;
use Translate\Filesystem\Dumper;
use Translate\Model\Entity\TranslateProject;
use Translate\Service\ExtractService;
use Translate\Service\PoAnalyzerService;
use Translate\Utility\ReferenceResolver;

/**
 * TranslateStrings Controller
 *
 * @property \Translate\Model\Table\TranslateStringsTable $TranslateStrings
 * @property \Translate\Controller\Component\TranslationComponent $Translation
 * @property \Search\Controller\Component\SearchComponent $Search
 */
class TranslateStringsController extends TranslateAppController {

	/**
	 * @var array<string, mixed>
	 */
	protected array $paginate = ['order' => ['TranslateStrings.modified' => 'DESC']];

	/**
	 * @return void
	 */
	public function initialize(): void {
		parent::initialize();

		$this->loadComponent('Search.Search', [
			'actions' => ['index'],
			'emptyValues' => [
				'missing_translation' => 0,
			],
		]);
	}

	/**
	 * Index method
	 *
	 * @return \Cake\Http\Response|null|void
	 */
	public function index() {
		$query = $this->TranslateStrings->find('search', search: $this->request->getQuery());
		$query->contain([
			'TranslateDomains',
		])->innerJoinWith('TranslateDomains', function ($q) {
			return $q->where([
				'TranslateDomains.translate_project_id' => $this->Translation->currentProjectId(),
				'TranslateDomains.active' => true,
			]);
		});
		$translateStrings = $this->paginate($query);

		$translateDomains = $this->TranslateStrings->TranslateDomains
			->find('list')
			->where([
				'translate_project_id' => $this->Translation->currentProjectId(),
				'active' => true,
			])
			->toArray();
		$this->set(compact('translateStrings', 'translateDomains'));
	}

	/**
	 * Orphaned strings - strings with no references to source code.
	 *
	 * @return \Cake\Http\Response|null|void
	 */
	public function orphaned() {
		$projectId = $this->Translation->currentProjectId();
		if ($projectId === null) {
			$this->Flash->error(__d('translate', 'No project selected.'));

			return $this->redirect(['controller' => 'Translate', 'action' => 'index']);
		}

		$query = $this->TranslateStrings->findOrphaned($projectId);
		$count = $query->count();

		// Handle bulk actions for ALL orphaned strings
		if ($this->request->is('post') && $count > 0) {
			$action = $this->request->getData('bulk_action');
			$orphanedIds = $this->TranslateStrings->findOrphaned($projectId)->select(['id'])->all()->extract('id')->toArray();

			if ($action === 'delete') {
				$deleted = $this->TranslateStrings->deleteAll(['id IN' => $orphanedIds]);
				$this->Flash->success(__d('translate', '{0} orphaned strings deleted.', $deleted));

				return $this->redirect(['action' => 'orphaned']);
			}
			if ($action === 'deactivate') {
				$updated = $this->TranslateStrings->updateAll(['active' => false], ['id IN' => $orphanedIds]);
				$this->Flash->success(__d('translate', '{0} orphaned strings marked as inactive.', $updated));

				return $this->redirect(['action' => 'orphaned']);
			}
		}

		$translateStrings = $this->paginate($query);

		$this->set(compact('translateStrings', 'count'));
	}

	/**
	 * View method
	 *
	 * @param string|null $id Translate String id.
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 * @return \Cake\Http\Response|null|void
	 */
	public function view($id = null) {
		$translateString = $this->TranslateStrings->get($id, [
			'contain' => ['TranslateDomains' => 'TranslateProjects', 'TranslateTerms' => 'TranslateLocales'],
		]);

		if ($translateString->translate_domain->translate_project_id !== $this->Translation->currentProjectId()) {
			throw new NotFoundException(__d('translate', 'String not found.'));
		}

		$this->set(compact('translateString'));
		//$this->set('_serialize', ['translateString']);
	}

	/**
	 * Add method
	 *
	 * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
	 */
	public function add() {
		$translateString = $this->TranslateStrings->newEmptyEntity();
		if ($this->request->is('post')) {
			$translateString = $this->TranslateStrings->patchEntity($translateString, $this->request->getData());
			if ($this->TranslateStrings->save($translateString)) {
				$this->Flash->success(__d('translate', 'The translate string has been saved.'));

				if ($this->request->getData('translate_afterwards')) {
					return $this->redirect(['action' => 'translate', $translateString->id]);
				}

				return $this->redirect(['action' => 'index']);
			}

			$this->Flash->error(__d('translate', 'The translate string could not be saved. Please, try again.'));
		}
		$translateDomains = $this->TranslateStrings->TranslateDomains
			->find('list')
			->where(['translate_project_id' => $this->Translation->currentProjectId()]);

		$this->set(compact('translateString', 'translateDomains'));
		//$this->set('_serialize', ['translateString']);
	}

	/**
	 * Edit method
	 *
	 * @param string|null $id Translate String id.
	 * @throws \Cake\Http\Exception\NotFoundException When record not found.
	 * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
	 */
	public function edit($id = null) {
		$translateString = $this->TranslateStrings->get($id, [
			'contain' => ['TranslateDomains'],
		]);

		if ($translateString->translate_domain->translate_project_id !== $this->Translation->currentProjectId()) {
			throw new NotFoundException(__d('translate', 'String not found.'));
		}

		$originalName = $translateString->name;

		if ($this->request->is(['patch', 'post', 'put'])) {
			$updateReferences = (bool)$this->request->getData('update_references');
			$translateString = $this->TranslateStrings->patchEntity($translateString, $this->request->getData());

			if ($this->TranslateStrings->save($translateString)) {
				// Update references in source files if requested and name changed
				if ($updateReferences && $originalName !== $translateString->name && Configure::read('debug')) {
					$updatedCount = $this->updateSourceReferences($translateString, $originalName);
					if ($updatedCount > 0) {
						$this->Flash->success(__d('translate', 'Updated {0} reference(s) in source files.', $updatedCount));
					}
				}

				$this->Flash->success(__d('translate', 'The translate string has been saved.'));

				if ($this->request->getData('translate_afterwards')) {
					return $this->redirect(['action' => 'translate', $id]);
				}

				return $this->redirect(['action' => 'index']);
			}

			$this->Flash->error(__d('translate', 'The translate string could not be saved. Please, try again.'));
		} else {
			foreach ($this->request->getQuery() as $key => $value) {
				$this->request = $this->request->withData((string)$key, (string)$value);
			}
		}

		$translateDomains = $this->TranslateStrings->TranslateDomains
			->find('list')
			->where(['translate_project_id' => $this->Translation->currentProjectId()]);

		$this->set(compact('translateString', 'translateDomains'));
		//$this->set('_serialize', ['translateString']);
	}

	/**
	 * Delete method
	 *
	 * @param string|null $id Translate String id.
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 * @return \Cake\Http\Response|null Redirects to index.
	 */
	public function delete($id = null) {
		$this->request->allowMethod(['post', 'delete']);
		$translateString = $this->TranslateStrings->get($id, [
			'contain' => ['TranslateDomains'],
		]);

		if ($translateString->translate_domain->translate_project_id !== $this->Translation->currentProjectId()) {
			throw new NotFoundException(__d('translate', 'String not found.'));
		}

		if ($this->TranslateStrings->delete($translateString)) {
			$this->Flash->success(__d('translate', 'The translate string has been deleted.'));
		} else {
			$this->Flash->error(__d('translate', 'The translate string could not be deleted. Please, try again.'));
		}

		return $this->redirect(['action' => 'index']);
	}

	/**
	 * @return void
	 */
	public function source() {
		//$sourceContent = $this->Translation->showSource(__FILE__, true);
		//$sourceContent = show_source(__FILE__, true);
		$sourceFile = __FILE__;
		$this->set(compact('sourceFile'));
	}

	/**
	 * @param \Translate\Service\ExtractService $extractService
	 *
	 * @return void
	 */
	public function extract(ExtractService $extractService) {
		$projectId = $this->Translation->currentProjectId();
		if (!$projectId) {
			throw new NotFoundException(__d('translate', 'No project selected.'));
		}

		// Get locale path for display
		$TranslateProjects = $this->fetchTable('Translate.TranslateProjects');
		$project = $TranslateProjects->get($projectId);
		$projectPath = $project->path ?? null;
		if (!$projectPath) {
			$projectPath = ROOT;
		} elseif (!str_starts_with($projectPath, '/')) {
			$projectPath = ROOT . DS . $projectPath;
		}
		$localePath = rtrim($projectPath, DS) . DS . 'resources' . DS . 'locales' . DS;

		// Set the locale path on the service
		$extractService->setLocalePath($localePath);

		$translateLocales = $this->TranslateStrings->TranslateTerms->TranslateLocales->getExtractableAsList($projectId);
		$poFileLanguages = $extractService->getPoFileLanguages();

		// Filter to only include files that actually exist
		$potFiles = [];
		foreach ($extractService->getPotFiles() as $potFile) {
			if (file_exists($localePath . $potFile . '.pot')) {
				$potFiles[$potFile] = $potFile;
			}
		}

		$poFiles = [];
		foreach ($poFileLanguages as $poFileLanguage) {
			$existingFiles = [];
			foreach ($extractService->getPoFiles($poFileLanguage) as $key => $poFile) {
				if (file_exists($localePath . $poFileLanguage . DS . $poFile . '.po')) {
					$existingFiles[$key] = $poFile;
				}
			}
			if ($existingFiles) {
				$poFiles[$poFileLanguage] = $existingFiles;
			}
		}

		if ($this->Translation->isPosted()) {
			$count = 0;
			$total = 0;
			$errors = [];

			foreach ((array)$this->request->getData('sel_pot') as $key => $domain) {
				if (!$domain) {
					continue;
				}
				if (!in_array($domain, $potFiles, true)) {
					continue;
				}
				$translations = $extractService->extractPotFile($domain);

				$translationDomain = $this->TranslateStrings->TranslateDomains->getDomain($projectId, $domain);

				foreach ($translations as $translation) {
					$total++;
					$success = (bool)$this->TranslateStrings->import($translation, $translationDomain->id);
					if (!$success) {
						$errors[] = '`' . h($translation['name']) . '` (' . h($domain) . ')';

						continue;
					}
					$count += (int)$success;
				}
			}

			foreach ((array)$this->request->getData('sel_po') as $key => $name) {
				$parts = explode('-', $name, 2);
				if (count($parts) < 2) {
					continue;
				}
				[$locale, $domain] = $parts;
				if (!$domain) {
					continue;
				}
				if (!isset($poFiles[$locale]) || !in_array($domain, $poFiles[$locale], true)) {
					continue;
				}
				$separatorPos = strpos($locale, '_');
				$lang = $separatorPos ? substr($locale, 0, $separatorPos) : $locale;
				if (!isset($translateLocales[$lang])) {
					// Auto-create language if it doesn't exist
					$languageName = ucfirst($lang);
					/** @var \Translate\Model\Entity\TranslateLocale|null $translateLocale */
					$translateLocale = $this->TranslateStrings->TranslateTerms->TranslateLocales->init(
						$languageName,
						$locale,
						$lang,
						$projectId,
					);
					if ($translateLocale) {
						$translateLocales[$lang] = $translateLocale->id;
					} else {
						continue;
					}
				}
				$translations = $extractService->extractPoFile($domain, $locale);

				$translationDomain = $this->TranslateStrings->TranslateDomains->getDomain($projectId, $domain);

				foreach ($translations as $translation) {
					$total++;
					$translationString = $this->TranslateStrings->import($translation, $translationDomain->id);
					if (!$translationString) {
						$errors[] = '`' . h($translation['name']) . '` (' . h($domain) . ')';

						continue;
					}

					$success = (bool)$this->TranslateStrings->TranslateTerms->import($translation, $translationString->id, $translateLocales[$lang]);
					if (!$success) {
						$errors[] = '`' . h($translation['name']) . '` (' . h($domain) . ')';

						continue;
					}
					$count += (int)$success;
				}
			}

			$this->Flash->success(__d('translate', 'Done: {0}/{1}', $count, $total));
			if ($errors) {
				$this->Flash->error(count($errors) . ' errors: ' . implode(', ', $errors));
			}
			//$this->redirect(array('action'=>'index'));

		} else {
			// For plugin projects, exclude default/cake domains from default selection
			$isPlugin = $project->type === TranslateProject::TYPE_PLUGIN;
			$excludeFromDefault = $isPlugin ? ['default', 'cake'] : [];

			$selPot = [];
			foreach ($potFiles as $key => $val) {
				if (!in_array($val, $excludeFromDefault, true)) {
					$selPot[] = $val;
				}
			}
			$this->request = $this->request->withData('sel_pot', $selPot);

			$selPo = [];
			foreach ($poFiles as $locale => $val) {
				if (!isset($translateLocales[$locale])) {
					continue;
				}
				foreach ($val as $k => $v) {
					if (!in_array($v, $excludeFromDefault, true)) {
						$selPo[] = $k;
					}
				}
			}
			$this->request = $this->request->withData('sel_po', $selPo);
		}

		$this->set(compact('potFiles', 'poFiles', 'localePath'));
	}

	/**
	 * @return \Cake\Http\Response|null|void
	 */
	public function dump() {
		$projectId = $this->Translation->currentProjectId();
		if (!$projectId) {
			throw new NotFoundException(__d('translate', 'No project selected.'));
		}

		// Get path from current project
		$TranslateProjects = $this->fetchTable('Translate.TranslateProjects');
		$project = $TranslateProjects->get($projectId);

		$path = $project->path ?? null;
		if (!$path) {
			$path = ROOT;
		} elseif (!str_starts_with($path, '/')) {
			$path = ROOT . DS . $path;
		}
		$path = rtrim($path, DS) . DS . 'resources' . DS . 'locales' . DS;

		$translateLocales = $this->TranslateStrings->TranslateTerms->TranslateLocales->getExtractableAsList($projectId);
		/** @var \Translate\Model\Entity\TranslateDomain[] $domains */
		$domains = $this->TranslateStrings->TranslateDomains->getActive()->toArray();

		$map = [];
		foreach ($translateLocales as $code => $id) {
			foreach ($domains as $domain) {
				$map[$code][$code . '_' . $domain->name] = $domain->name;
			}
		}

		if ($this->Translation->isPosted() && $this->request->getData('domains')) {
			$count = 0;
			$errors = [];
			/** @var array<string> $postedDomains */
			$postedDomains = (array)$this->request->getData('domains');
			foreach ($postedDomains as $key => $domain) {
				[$lang, $domain] = explode('_', $domain, 2);

				$langId = $this->TranslateStrings->TranslateTerms->TranslateLocales->find()->where(['iso2' => $lang])->firstOrFail()->id;
				$domainId = $this->TranslateStrings->TranslateDomains->find()->where(['name' => $domain, 'translate_project_id' => $projectId])->firstOrFail()->id;
				$translations = $this->TranslateStrings->TranslateTerms->getTranslations($langId, $domainId)->toArray();

				if (!$translations) {
					continue;
				}

				$dumper = new Dumper();
				if (!$dumper->dump($translations, $domain, $lang, $path)) {
					$errors[] = $lang . '/' . $domain;

					continue;
				}

				$count++;
			}

			$this->Flash->success('Done: ' . $count . ' files');
			if ($errors) {
				$this->Flash->error(count($errors) . ' errors: ' . implode(', ', $errors));
			}

		} elseif ($this->Translation->isPosted() && !$map) {
			$this->Flash->warning('Please activate a domain for dumping.');

			return $this->redirect(['controller' => 'TranslateDomains', 'action' => 'index']);
		} elseif (!$this->Translation->isPosted()) {
			$domainArray = [];
			foreach ($translateLocales as $code => $id) {
				/** @var \Translate\Model\Entity\TranslateDomain $domain */
				foreach ($domains as $domain) {
					$domainArray[] = $code . '_' . $domain->name;
				}
			}
			$this->request = $this->request->withData('domains', $domainArray);
		}

		$this->set(compact('map', 'path'));
	}

	/**
	 * Main translation view
	 * - all needed language boxes
	 *
	 * @param int|null $id
	 *
	 * @return \Cake\Http\Response|null|void
	 */
	public function translate($id = null) {
		if (!$id) {
			$next = $this->TranslateStrings->getNext(null, null)->first();
			if (!empty($next['id'])) {
				return $this->redirect([$next['id']]);
			}

			$this->Flash->success('No more open translations.');

			return $this->redirect(['controller' => 'Translate', 'action' => 'index']);
		}

		$translateString = $this->TranslateStrings->get($id, ['contain' => ['TranslateDomains' => 'TranslateProjects']]);

		if ($translateString->translate_domain->translate_project_id !== $this->Translation->currentProjectId()) {
			throw new NotFoundException(__d('translate', 'String not found.'));
		}

		/** @var \Translate\Model\Entity\TranslateLocale[] $translateLocales */
		$translateLocales = $this->TranslateStrings->TranslateTerms->TranslateLocales->find()
			->where(['translate_project_id' => $this->Translation->currentProjectId()])
			->all()
			->toArray();
		if (!$translateLocales) {
			$this->Flash->error(__d('translate', 'You need at least one language to translate'));

			return $this->redirect(['controller' => 'TranslateLocales', 'action' => 'add']);
		}

		$translateTerms = $this->TranslateStrings->TranslateTerms->getTranslatedArray($id);

		if ($this->Translation->isPosted()) {
			if ($this->request->getData('skip')) {
				$translateString->skipped = true;
				$this->TranslateStrings->saveOrFail($translateString);

				// Try to find next in same domain first
				$next = $this->TranslateStrings->getNext($translateString->translate_domain_id, null)
					->contain(['TranslateDomains'])
					->where(['TranslateStrings.id >' => $id])
					->orderBy(['TranslateStrings.id' => 'ASC'])
					->first();

				// If no more in this domain, try any domain
				if (!$next) {
					$next = $this->TranslateStrings->getNext(null, null)
						->contain(['TranslateDomains'])
						->where(['TranslateStrings.id >' => $id])
						->orderBy(['TranslateStrings.id' => 'ASC'])
						->first();
				}

				if ($next) {
					return $this->redirect(['action' => 'translate', $next->id]);
				}

				$this->Flash->success(__d('translate', 'No more open translations.'));

				return $this->redirect(['action' => 'index']);
			}

			$success = true;
			foreach ($translateLocales as $translateLocale) {
				$key = strtolower($translateLocale->locale);
				$term = $this->request->getData('content_' . $key);
				if ($term !== null) {
					if (!isset($translateTerms[$translateLocale->id])) {
						$translateTerm = $this->TranslateStrings->TranslateTerms->newEmptyEntity();
					} else {
						$translateTerm = $translateTerms[$translateLocale->id];
					}

					$data = [
						'translate_string_id' => $id,
						'translate_locale_id' => $translateLocale->id,
						'content' => $term,
						//'user_id' => $this->AuthUser->id()
						'string' => $translateString->name,
					];
					if ($translateString->plural !== null) {
						//TODO allow more plurals
						$data['plural_2'] = $this->request->getData('plural_2_' . $key);
					}

					$translateTerm = $this->TranslateStrings->TranslateTerms->patchEntity($translateTerm, $data);
					if (!$this->TranslateStrings->TranslateTerms->save($translateTerm)) {
						$translateString->setError('content_' . $key, $translateTerm->getError('content'));
						$success = false;
					}
				}
			}

			if ($success) {
				if (array_key_exists('next', $this->request->getData())) {
					// Try to find next in same domain first
					$next = $this->TranslateStrings->getNext($translateString->translate_domain_id, null)
						->contain(['TranslateDomains'])
						->where(['TranslateStrings.id >' => $id])
						->orderBy(['TranslateStrings.id' => 'ASC'])
						->first();

					// If no more in this domain, try any domain
					if (!$next) {
						$next = $this->TranslateStrings->getNext(null, null)
							->contain(['TranslateDomains'])
							->where(['TranslateStrings.id >' => $id])
							->orderBy(['TranslateStrings.id' => 'ASC'])
							->first();
					}

					if ($next) {
						return $this->redirect(['action' => 'translate', $next->id]);
					}

					$this->Flash->success(__d('translate', 'No more open translations.'));

					return $this->redirect(['action' => 'index']);
				}

				$this->Flash->success(__d('translate', 'Translation saved successfully.'));

				return $this->redirect(['action' => 'translate', $id]);
			}
		} else {
			foreach ($translateTerms as $translateTerm) {
				$key = $this->TranslateStrings->resolveLanguageKey($translateTerm->translate_locale_id, $translateLocales);
				$this->request = $this->request->withData('content_' . $key, $translateTerm->content);
				$this->request = $this->request->withData('plural_2_' . $key, $translateTerm->plural_2);
			}
		}

		$suggestions = $this->TranslateStrings->getSuggestions($translateString, $translateLocales, $translateTerms);
		//$pluralSuggestions =

		$this->set(compact('translateString', 'translateLocales', 'suggestions'));
	}

	/**
	 * @param int $id
	 * @param int $reference 0 based
	 * @throws \Cake\Http\Exception\NotFoundException
	 * @return \Cake\Http\Response|null|void
	 */
	public function displayReference(int $id, int $reference) {
		if ($this->request->is('ajax')) {
			$this->viewBuilder()->setLayout('ajax');
		}

		$translateString = $this->TranslateStrings->get($id, ['contain' => ['TranslateDomains' => 'TranslateProjects']]);

		if ($translateString->translate_domain->translate_project_id !== $this->Translation->currentProjectId()) {
			throw new NotFoundException(__d('translate', 'String not found.'));
		}

		$referenceString = ReferenceResolver::getReferenceByIndex($translateString->references, $reference);
		$parsed = ReferenceResolver::parseReference($referenceString);
		$referencePath = $parsed['path'];
		$lines = $parsed['lines'];

		$projectPath = $translateString->translate_domain->translate_project->path ?? null;
		$file = ReferenceResolver::resolveFilePath($referencePath, $projectPath);

		// Handle POST request to edit source file (debug mode only)
		if ($this->request->is('post') && Configure::read('debug')) {
			$newContent = $this->request->getData('file_content');
			if ($newContent !== null) {
				if (!is_writable($file)) {
					$this->Flash->error(__d('translate', 'File is not writable: {0}', $file));
				} else {
					$success = file_put_contents($file, $newContent);
					if ($success !== false) {
						$this->Flash->success(__d('translate', 'File updated successfully. Please commit your changes!'));
					} else {
						$this->Flash->error(__d('translate', 'Failed to write file'));
					}
				}
			}
		}

		if (!file_exists($file)) {
			throw new NotFoundException(__d('translate', 'Source file not found: {0}', $referencePath));
		}

		$fileArray = file($file);
		$fileContent = file_get_contents($file);
		$canEdit = Configure::read('debug') && Configure::read('Translate.editor') && is_writable($file);

		$this->set(compact('fileArray', 'fileContent', 'lines', 'referencePath', 'canEdit', 'translateString', 'reference', 'id'));
	}

	/**
	 * Import from blob or other source/file
	 *
	 * @return \Cake\Http\Response|null
	 */
	public function import() {
		return $this->redirect(['action' => 'extract']);
	}

	/**
	 * Update all source code references when a translation string is changed
	 *
	 * @param \Translate\Model\Entity\TranslateString $translateString
	 * @param string $originalName
	 * @return int Number of updated references
	 */
	protected function updateSourceReferences($translateString, string $originalName): int {
		$updatedCount = 0;

		if (!$translateString->references) {
			return 0;
		}

		// Parse references
		$references = explode(PHP_EOL, $translateString->references);
		$path = $translateString->translate_domain->translate_project->path ?? null;
		if (!$path) {
			$path = ROOT;
		} elseif (!str_starts_with($path, '/')) {
			$path = ROOT . DS . $path;
		}
		$path = rtrim((string)realpath($path), '/') . '/';

		if (!is_dir($path)) {
			$this->Flash->warning(__d('translate', 'Project path not found: {0}', $translateString->translate_domain->translate_project->path ?? 'ROOT'));

			return 0;
		}

		foreach ($references as $reference) {
			$reference = trim($reference);
			if ($reference === '') {
				continue;
			}

			[$referencePath] = explode(':', $reference);
			$file = $path . $referencePath;

			if (!file_exists($file)) {
				continue;
			}

			if (!is_writable($file)) {
				$this->Flash->warning(__d('translate', 'File not writable: {0}', $referencePath));

				continue;
			}

			// Read file content
			$content = file_get_contents($file);
			if ($content === false) {
				continue;
			}
			$originalContent = $content;

			// Build patterns to match different translation function calls
			// Matches: __('text'), __d('domain', 'text'), __x('context', 'text'), __dx('domain', 'context', 'text')
			$escapedOriginal = preg_quote($originalName, '/');
			$escapedNew = addcslashes($translateString->name, '\\$');

			// Pattern for single-quoted strings
			$pattern1 = "/(__d?x?)\s*\(\s*(?:'[^']*'\s*,\s*)?(?:'[^']*'\s*,\s*)?'{$escapedOriginal}'/";
			$replacement1 = "$1('{$escapedNew}'";

			// Pattern for double-quoted strings
			$pattern2 = "/(__d?x?)\s*\(\s*(?:\"[^\"]*\"\s*,\s*)?(?:\"[^\"]*\"\s*,\s*)?\"{$escapedOriginal}\"/";
			$replacement2 = "$1(\"{$escapedNew}\"";

			// Apply replacements
			$newContent = preg_replace($pattern1, $replacement1, $content);
			if ($newContent !== null) {
				$content = $newContent;
			}
			$newContent = preg_replace($pattern2, $replacement2, $content);
			if ($newContent !== null) {
				$content = $newContent;
			}

			// Only write if content changed
			if ($content !== $originalContent) {
				if (file_put_contents($file, $content) !== false) {
					$updatedCount++;
				}
			}
		}

		return $updatedCount;
	}

	/**
	 * Analyze PO/POT file content for issues.
	 *
	 * @param \Translate\Service\ExtractService $extractService
	 * @return \Cake\Http\Response|null|void
	 */
	public function analyze(ExtractService $extractService) {
		$result = null;
		$content = '';
		$selectedFile = null;

		// Get locale path for file existence checks
		$TranslateProjects = $this->fetchTable('Translate.TranslateProjects');
		$project = $TranslateProjects->get($this->Translation->currentProjectId());
		$projectPath = $project->path ?? null;
		if (!$projectPath) {
			$projectPath = ROOT;
		} elseif (!str_starts_with($projectPath, '/')) {
			$projectPath = ROOT . DS . $projectPath;
		}
		$localePath = rtrim($projectPath, DS) . DS . 'resources' . DS . 'locales' . DS;
		$extractService->setLocalePath($localePath);

		// Get available PO/POT files (same as extract action)
		$potFiles = $extractService->getPotFiles();
		$poFileLanguages = $extractService->getPoFileLanguages();
		$poFiles = [];
		foreach ($poFileLanguages as $poFileLanguage) {
			$poFiles[$poFileLanguage] = $extractService->getPoFiles($poFileLanguage);
		}

		// Build flat list for dropdown, only include existing files
		$availableFiles = [];
		foreach ($potFiles as $potFile) {
			$filePath = $localePath . $potFile . '.pot';
			if (file_exists($filePath)) {
				$availableFiles['pot:' . $potFile] = $potFile . '.pot';
			}
		}
		foreach ($poFiles as $locale => $files) {
			foreach ($files as $key => $file) {
				$filePath = $localePath . $locale . DS . $file . '.po';
				if (file_exists($filePath)) {
					$availableFiles['po:' . $locale . ':' . $file] = $locale . '/' . $file . '.po';
				}
			}
		}

		// Check for query string selection
		$fileParam = $this->request->getQuery('file');
		if ($fileParam && isset($availableFiles[$fileParam])) {
			$selectedFile = $fileParam;
			$content = $this->readPoFile($fileParam, $extractService);
			if ($content) {
				$analyzer = new PoAnalyzerService();
				$result = $analyzer->analyze($content);
			}
		} elseif ($this->Translation->isPosted()) {
			// Handle form submission
			$selectedFile = $this->request->getData('selected_file');
			if ($selectedFile) {
				$content = $this->readPoFile((string)$selectedFile, $extractService);
			} else {
				$content = (string)$this->request->getData('content');
				$file = $this->request->getUploadedFile('file');

				// Handle file upload
				if ($file && $file->getError() === UPLOAD_ERR_OK) {
					$content = (string)$file->getStream();
				}
			}

			if ($content) {
				$keyBasedMode = $this->request->getData('key_based');
				$keyBasedMode = $keyBasedMode === '' ? null : (bool)$keyBasedMode;

				$analyzer = new PoAnalyzerService();
				$result = $analyzer->analyze($content, $keyBasedMode);
			} else {
				$this->Flash->error(__d('translate', 'Please provide PO file content or upload a file.'));
			}
		}

		$this->set(compact('result', 'content', 'availableFiles', 'selectedFile'));
	}

	/**
	 * Read PO/POT file content based on selection key.
	 *
	 * @param string $fileKey Format: "pot:domain" or "po:locale:domain"
	 * @param \Translate\Service\ExtractService $extractService
	 * @return string|null
	 */
	protected function readPoFile(string $fileKey, ExtractService $extractService): ?string {
		$parts = explode(':', $fileKey);
		$type = $parts[0];

		// Get path from current project
		$TranslateProjects = $this->fetchTable('Translate.TranslateProjects');
		$project = $TranslateProjects->get($this->Translation->currentProjectId());
		$path = $project->path ?? null;
		if (!$path) {
			$path = ROOT;
		} elseif (!str_starts_with($path, '/')) {
			$path = ROOT . DS . $path;
		}
		$localePath = rtrim($path, DS) . DS . 'resources' . DS . 'locales' . DS;

		if ($type === 'pot' && isset($parts[1])) {
			$filePath = $localePath . $parts[1] . '.pot';
		} elseif ($type === 'po' && isset($parts[1], $parts[2])) {
			$filePath = $localePath . $parts[1] . DS . $parts[2] . '.po';
		} else {
			return null;
		}

		if (!file_exists($filePath)) {
			$this->Flash->error(__d('translate', 'File not found: {0}', $filePath));

			return null;
		}

		return file_get_contents($filePath) ?: null;
	}

	/**
	 * Run CakePHP i18n extract command (experimental).
	 *
	 * This allows running the extraction from the web interface.
	 *
	 * @return \Cake\Http\Response|null|void
	 */
	public function runExtract() {
		$projectId = $this->Translation->currentProjectId();
		if (!$projectId) {
			throw new NotFoundException(__d('translate', 'No project selected.'));
		}

		$TranslateProjects = $this->fetchTable('Translate.TranslateProjects');
		/** @var \Translate\Model\Entity\TranslateProject $project */
		$project = $TranslateProjects->get($projectId);

		$appPath = $project->path ?: null;
		if (!$appPath) {
			$appPath = ROOT;
		} elseif (!str_starts_with($appPath, '/')) {
			$appPath = ROOT . DS . $appPath;
		}
		$appPath = rtrim($appPath, DS);

		// CakePHP 5+ uses resources/locales, legacy uses Locale/
		$legacyPath = $appPath . DS . 'Locale' . DS;
		$modernPath = $appPath . DS . 'resources' . DS . 'locales' . DS;
		// Prefer modern path, only use legacy if it exists and modern doesn't
		if (is_dir($legacyPath) && !is_dir($modernPath)) {
			$localePath = $legacyPath;
		} else {
			$localePath = $modernPath;
		}

		$output = null;
		$command = null;
		$returnCode = null;
		$dryRunResults = null;

		if ($this->request->is('post')) {
			$paths = $this->request->getData('paths');
			// Textarea sends a single string with newlines, split it into array
			if (is_string($paths)) {
				$paths = explode("\n", $paths);
			}
			$paths = array_filter(array_map('trim', (array)$paths));
			if (!$paths) {
				$paths = [$appPath . DS . 'src', $appPath . DS . 'templates'];
			}
			// Convert relative paths to absolute and validate they exist
			$validPaths = [];
			foreach ($paths as $path) {
				if (!str_starts_with($path, '/')) {
					$path = $appPath . DS . $path;
				}
				if (is_dir($path)) {
					$validPaths[] = $path;
				}
			}
			$paths = $validPaths;

			if (!$paths) {
				$this->Flash->error(__d('translate', 'No valid paths found to scan.'));

				$defaultPaths = ['src', 'templates'];
				$isPlugin = $project->type === TranslateProject::TYPE_PLUGIN;
				$this->set(compact('appPath', 'localePath', 'defaultPaths', 'output', 'command', 'returnCode', 'isPlugin', 'dryRunResults'));

				return;
			}

			$outputPath = $this->request->getData('output_path') ?: $localePath;
			// Convert relative output path to absolute
			if (!str_starts_with($outputPath, '/')) {
				$outputPath = $appPath . DS . $outputPath;
			}
			$merge = $this->request->getData('merge') ? 'yes' : 'no';
			$overwrite = $this->request->getData('overwrite') ? 'yes' : 'no';
			$extractCore = $this->request->getData('extract_core') ? 'yes' : 'no';
			$dryRun = (bool)$this->request->getData('dry_run');

			// For plugins, determine the expected domain from project name
			$isPlugin = $project->type === TranslateProject::TYPE_PLUGIN;
			$pluginDomain = $isPlugin ? Inflector::underscore($project->name) : null;

			// Always extract to temp directory first (avoids weird behavior differences)
			$tempDir = sys_get_temp_dir() . DS . 'translate_extract_' . uniqid();
			mkdir($tempDir, 0755, true);
			$actualOutputPath = $tempDir;
			$merge = 'no'; // Always fresh extraction to temp

			// Ensure final output directory exists for non-dry run
			if (!$dryRun) {
				if (!is_dir($outputPath)) {
					if (!mkdir($outputPath, 0755, true)) {
						$this->Flash->error(__d('translate', 'Failed to create output directory: {0}', $outputPath));

						$defaultPaths = ['src', 'templates'];
						$isPlugin = $project->type === TranslateProject::TYPE_PLUGIN;
						$pluginDomain = $isPlugin ? Inflector::underscore($project->name) : null;
						$this->set(compact('appPath', 'localePath', 'defaultPaths', 'output', 'command', 'returnCode', 'isPlugin', 'dryRunResults', 'pluginDomain'));

						return;
					}
				}
				if (!is_writable($outputPath)) {
					$this->Flash->error(__d('translate', 'Output directory is not writable: {0}', $outputPath));

					$defaultPaths = ['src', 'templates'];
					$isPlugin = $project->type === TranslateProject::TYPE_PLUGIN;
					$pluginDomain = $isPlugin ? Inflector::underscore($project->name) : null;
					$this->set(compact('appPath', 'localePath', 'defaultPaths', 'output', 'command', 'returnCode', 'isPlugin', 'dryRunResults', 'pluginDomain'));

					return;
				}
			}

			// Build command arguments for display
			$pathsArg = implode(',', $paths);
			$command = sprintf(
				'bin/cake i18n extract --paths %s --output %s --merge %s --overwrite %s --extract-core %s',
				escapeshellarg($pathsArg),
				escapeshellarg(rtrim($outputPath, DS)),
				escapeshellarg($merge),
				escapeshellarg($overwrite),
				escapeshellarg($extractCore),
			);

			// Run command directly via PHP instead of shell
			$args = [
				'--paths',
				$pathsArg,
				'--output',
				rtrim($actualOutputPath, DS),
				'--merge',
				$merge,
				'--overwrite',
				$overwrite,
				'--extract-core',
				$extractCore,
			];

			// Debug: list files before extraction in final output dir
			$filesBefore = glob($outputPath . DS . '*.pot') ?: [];

			// Debug: show exact args
			$debugArgs = $args;

			// Capture output using temp files (ConsoleOutput doesn't work with php://temp streams)
			$outputFile = sys_get_temp_dir() . DS . 'translate_out_' . uniqid() . '.txt';
			$errorFile = sys_get_temp_dir() . DS . 'translate_err_' . uniqid() . '.txt';
			touch($outputFile);
			touch($errorFile);

			$io = new ConsoleIo(
				new ConsoleOutput($outputFile),
				new ConsoleOutput($errorFile),
			);

			try {
				$extractCommand = new I18nExtractCommand();
				$returnCode = $extractCommand->run($args, $io);

				// Read captured output
				$output = file_get_contents($outputFile) ?: '';
				$errors = file_get_contents($errorFile) ?: '';

				// Prepend debug args
				$output = 'Args: ' . implode(' ', $debugArgs) . "\n\n" . $output;

				if ($errors) {
					$output .= "\n\nSTDERR:\n" . $errors;
				}

				// Debug: list files after extraction in temp directory
				$filesAfterTemp = glob($tempDir . DS . '*.pot') ?: [];
				$output .= "\n\n--- DEBUG INFO ---\n";
				$output .= 'Paths scanned: ' . $pathsArg . "\n";
				$output .= 'Temp output path: ' . $tempDir . "\n";
				$output .= 'Final output path: ' . $outputPath . "\n";
				$output .= 'Merge: ' . $merge . ', Overwrite: ' . $overwrite . "\n";
				$output .= 'Return code: ' . var_export($returnCode, true) . ' (CODE_SUCCESS = ' . Command::CODE_SUCCESS . ")\n";
				$output .= 'Is dry run: ' . ($dryRun ? 'yes' : 'no') . "\n";
				$output .= 'Files in final dir before: ' . (empty($filesBefore) ? '(none)' : implode(', ', array_map('basename', $filesBefore))) . "\n";
				$output .= 'Files in temp after extraction: ' . (empty($filesAfterTemp) ? '(none)' : implode(', ', array_map('basename', $filesAfterTemp))) . "\n";
				if ($pluginDomain) {
					$output .= 'Expected plugin domain: ' . $pluginDomain . ".pot\n";
				}

				// Collect generated POT files from temp directory
				$potFiles = glob($tempDir . DS . '*.pot') ?: [];

				// For dry run, show preview
				if ($dryRun) {
					$dryRunResults = [];
					foreach ($potFiles as $potFile) {
						$filename = basename($potFile);
						// For plugins, only show the plugin's domain POT file
						if ($pluginDomain !== null && $filename !== $pluginDomain . '.pot') {
							continue;
						}
						$content = (string)file_get_contents($potFile);
						// Count msgid entries
						preg_match_all('/^msgid\s+"/m', $content, $matches);
						$count = count($matches[0]) - 1; // Subtract 1 for the header
						$dryRunResults[$filename] = [
							'count' => max(0, $count),
							'content' => $content,
						];
					}

					$this->Flash->info(__d('translate', 'Dry run completed. Preview of extracted strings shown below.'));
				} elseif ($returnCode === Command::CODE_SUCCESS) {
					// Copy files from temp to final output directory
					$copiedFiles = [];
					foreach ($potFiles as $potFile) {
						$filename = basename($potFile);
						// For plugins, only copy the plugin's domain POT file
						if ($pluginDomain !== null && $filename !== $pluginDomain . '.pot') {
							continue;
						}
						$destFile = $outputPath . DS . $filename;
						if (copy($potFile, $destFile)) {
							$copiedFiles[] = $destFile;
						}
					}

					// Show info about copied files
					if ($copiedFiles) {
						$fileInfo = [];
						foreach ($copiedFiles as $file) {
							$content = (string)file_get_contents($file);
							preg_match_all('/^msgid\s+"/m', $content, $matches);
							$count = max(0, count($matches[0]) - 1);
							$fileInfo[] = basename($file) . ' (' . $count . ' strings)';
						}
						$this->Flash->success(__d('translate', 'Extraction completed. Generated: {0}', implode(', ', $fileInfo)));

						// Direct import to database if requested
						$directImport = (bool)$this->request->getData('direct_import');
						if ($directImport) {
							$extractService = new ExtractService($outputPath);
							$importCount = 0;
							$importTotal = 0;
							$importErrors = [];

							foreach ($copiedFiles as $file) {
								$domain = pathinfo($file, PATHINFO_FILENAME);
								$translations = $extractService->extractPotFile($domain);
								$translationDomain = $this->TranslateStrings->TranslateDomains->getDomain(
									$projectId,
									$domain,
								);

								foreach ($translations as $translation) {
									$importTotal++;
									$success = (bool)$this->TranslateStrings->import($translation, $translationDomain->id);
									if (!$success) {
										$importErrors[] = '`' . h($translation['name']) . '` (' . h($domain) . ')';

										continue;
									}
									$importCount += (int)$success;
								}
							}

							if ($importErrors) {
								$this->Flash->warning(__d('translate', 'Import partially completed: {0} of {1} strings imported. Errors: {2}', $importCount, $importTotal, implode(', ', array_slice($importErrors, 0, 5))));
							} else {
								$this->Flash->success(__d('translate', 'Import completed: {0} of {1} strings imported to database.', $importCount, $importTotal));
							}
						}
					} else {
						$this->Flash->warning(__d('translate', 'Extraction completed but no POT files were generated. Check if your code uses __d(\'{0}\', ...) calls.', $pluginDomain ?? 'default'));
					}
				} else {
					$this->Flash->warning(__d('translate', 'Extraction completed with return code: {0}', $returnCode));
				}
			} catch (Throwable $e) {
				$this->Flash->error(__d('translate', 'Extraction failed: {0}', $e->getMessage()));
				$output = $e->getMessage() . "\n" . $e->getTraceAsString();
				$returnCode = 1;
			} finally {
				// Clean up temp files
				if (file_exists($outputFile)) {
					unlink($outputFile);
				}
				if (file_exists($errorFile)) {
					unlink($errorFile);
				}

				// Clean up temp directory for dry run
				if (is_dir($tempDir)) {
					$files = glob($tempDir . DS . '*') ?: [];
					foreach ($files as $file) {
						unlink($file);
					}
					rmdir($tempDir);
				}
			}
		}

		$defaultPaths = [
			'src',
			'templates',
		];

		$isPlugin = $project->type === TranslateProject::TYPE_PLUGIN;
		$pluginDomain = $isPlugin ? Inflector::underscore($project->name) : null;

		$this->set(compact('appPath', 'localePath', 'defaultPaths', 'output', 'command', 'returnCode', 'isPlugin', 'dryRunResults', 'pluginDomain'));
	}

}
