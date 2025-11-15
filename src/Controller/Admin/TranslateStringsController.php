<?php

namespace Translate\Controller\Admin;

use Cake\Core\Configure;
use Cake\Http\Exception\NotFoundException;
use Translate\Controller\TranslateAppController;
use Translate\Filesystem\Dumper;
use Translate\Service\ExtractService;

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
		]);
		$translateStrings = $this->paginate($query);

		$translateDomains = $this->TranslateStrings->TranslateDomains
			->find('list')
			->where(['translate_project_id' => $this->Translation->currentProjectId()])
			->toArray();
		$this->set(compact('translateStrings', 'translateDomains'));
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
		$translateDomains = $this->TranslateStrings->TranslateDomains->find('list');

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

		$translateDomains = $this->TranslateStrings->TranslateDomains->find('list');

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
		$translateString = $this->TranslateStrings->get($id);
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
		$potFiles = $extractService->getPotFiles();
		$translateLocales = $this->TranslateStrings->TranslateTerms->TranslateLocales->getExtractableAsList($this->Translation->currentProjectId());
		$poFileLanguages = $extractService->getPoFileLanguages();
		$poFiles = [];
		foreach ($poFileLanguages as $poFileLanguage) {
			$poFiles[$poFileLanguage] = $extractService->getPoFiles($poFileLanguage);
		}

		if ($this->Translation->isPosted()) {
			$count = 0;
			$errors = [];

			foreach ((array)$this->request->getData('sel_pot') as $key => $domain) {
				if (!$domain) {
					continue;
				}
				if (!in_array($domain, $potFiles, true)) {
					continue;
				}
				$translations = $extractService->extractPotFile($domain);

				$translationDomain = $this->TranslateStrings->TranslateDomains->getDomain($this->Translation->currentProjectId(), $domain);

				foreach ($translations as $translation) {
					$success = (bool)$this->TranslateStrings->import($translation, $translationDomain->id);
					if (!$success) {
						$errors[] = '`' . h($translation['name']) . '`';

						continue;
					}
					$count += (int)$success;
				}
			}

			foreach ((array)$this->request->getData('sel_po') as $key => $name) {
				[$locale, $domain] = explode('-', $name, 2);
				if (!$domain) {
					continue;
				}
				if (!isset($poFiles[$locale][$locale . '-' . $domain])) {
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
						$this->Translation->currentProjectId(),
					);
					if ($translateLocale) {
						$translateLocales[$lang] = $translateLocale->id;
					} else {
						continue;
					}
				}
				$translations = $extractService->extractPoFile($domain, $locale);

				$translationDomain = $this->TranslateStrings->TranslateDomains->getDomain($this->Translation->currentProjectId(), $domain);

				foreach ($translations as $translation) {
					$translationString = $this->TranslateStrings->import($translation, $translationDomain->id);
					if (!$translationString) {
						$errors[] = '`' . h($translation['name']) . '`';
					}

					$success = (bool)$this->TranslateStrings->TranslateTerms->import($translation, $translationString->id, $translateLocales[$lang]);
					if (!$success) {
						$errors[] = '`' . h($translation['name']) . '`';

						continue;
					}
					$count += (int)$success;
				}
			}

			$this->Flash->success('Done: ' . $count);
			if ($errors) {
				$this->Flash->error(count($errors) . ' errors: ' . implode(', ', $errors));
			}
			//$this->redirect(array('action'=>'index'));

		} else {
			$selPot = [];
			foreach ($potFiles as $key => $val) {
				$selPot[] = $val;
			}
			$this->request = $this->request->withData('sel_pot', $selPot);

			$selPo = [];
			foreach ($poFiles as $locale => $val) {
				if (!isset($translateLocales[$locale])) {
					continue;
				}
				foreach ($val as $k => $v) {
					$selPo[] = $k;
				}
			}
			$this->request = $this->request->withData('sel_po', $selPo);
		}

		$this->set(compact('potFiles', 'poFiles'));
	}

	/**
	 * @return \Cake\Http\Response|null|void
	 */
	public function dump() {
		$translateLocales = $this->TranslateStrings->TranslateTerms->TranslateLocales->getExtractableAsList($this->Translation->currentProjectId());
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
				$domainId = $this->TranslateStrings->TranslateDomains->find()->where(['name' => $domain, 'translate_project_id' => $this->Translation->currentProjectId()])->firstOrFail()->id;
				$translations = $this->TranslateStrings->TranslateTerms->getTranslations($langId, $domainId)->toArray();

				if (!$translations) {
					continue;
				}

				$dumper = new Dumper();
				if (!$dumper->dump($translations, $domain, $lang)) {
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

		$this->set(compact('map'));
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

		/** @var \Translate\Model\Entity\TranslateLocale[] $translateLocales */
		$translateLocales = $this->TranslateStrings->TranslateTerms->TranslateLocales->find()->all()->toArray();
		if (!$translateLocales) {
			$this->Flash->error(__d('translate', 'You need at least one language to translate'));

			return $this->redirect(['controller' => 'TranslateLocales', 'action' => 'add']);
		}

		$translateTerms = $this->TranslateStrings->TranslateTerms->getTranslatedArray($id);

		if ($this->Translation->isPosted()) {
			if ($this->request->getData('skip')) {
				$translateString->skipped = true;
				$this->TranslateStrings->saveOrFail($translateString);

				$next = $this->TranslateStrings->getNext($translateString->translate_domain_id, $translateString->id)->first();
				if (!empty($next['id'])) {
					return $this->redirect([$next['id']]);
				}

				$this->Flash->success('No more open translations for domain `' . h($translateString->translate_domain->name) . '`.');

				return $this->redirect(['action' => 'view', $id]);
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
					$next = $this->TranslateStrings->getNext($translateString->translate_domain_id, $id)->first();
					if (!empty($next['id'])) {
						return $this->redirect([$next['id']]);
					}

					$this->Flash->success('No more open translations for domain `' . h($translateString->translate_domain->name) . '`.');
				}

				return $this->redirect(['action' => 'view', $id]);
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

		$translateString = $this->TranslateStrings->get($id, ['contain' => ['TranslateDomains']]);

		$sep = explode(PHP_EOL, $translateString->references);
		$occ = [];
		foreach ($sep as $s) {
			$s = trim($s);
			if ($s !== '') {
				$occ[] = $s;
			}
		}
		if (!isset($occ[$reference])) {
			throw new NotFoundException('Could not find reference `' . $reference . '`');
		}

		$referenceString = $occ[$reference];
		$parts = explode(':', $referenceString, 2);
		$referencePath = $parts[0];
		$lines = isset($parts[1]) ? explode(';', $parts[1]) : [];

		$path = $translateString->translate_domain->path;
		if (!str_starts_with($path, '/')) {
			$path = ROOT . DS . $path;
		}
		$path = rtrim((string)realpath($path), '/') . '/';
		if (!is_dir($path)) {
			throw new NotFoundException('Path not found: ' . $translateString->translate_domain->path);
		}

		$file = $path . $referencePath;
		if (!file_exists($file)) {
			throw new NotFoundException('File not found: ' . $file);
		}

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

		$fileArray = file($file);
		$fileContent = file_get_contents($file);
		$canEdit = Configure::read('debug') && is_writable($file);

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

		// Parse references
		$references = explode(PHP_EOL, $translateString->references);
		$path = $translateString->translate_domain->path;
		if (!str_starts_with($path, '/')) {
			$path = ROOT . DS . $path;
		}
		$path = rtrim((string)realpath($path), '/') . '/';

		if (!is_dir($path)) {
			$this->Flash->warning(__d('translate', 'Domain path not found: {0}', $translateString->translate_domain->path));

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

}
