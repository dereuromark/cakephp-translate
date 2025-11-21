<?php

namespace Translate\Controller;

use Cake\Http\Exception\NotFoundException;
use Translate\Model\Entity\TranslateProject;

/**
 * @property \Translate\Model\Table\TranslateDomainsTable $TranslateDomains
 * @property \Translate\Controller\Component\TranslationComponent $Translation
 * @property \Search\Controller\Component\SearchComponent $Search
 */
class TranslateController extends TranslateAppController {

	/**
	 * @var string|null
	 */
	protected ?string $defaultTable = 'Translate.TranslateDomains';

	/**
	 * @return void
	 */
	public function initialize(): void {
		parent::initialize();

		$this->loadComponent('Search.Search', [
			'actions' => ['terms'],
		]);
	}

	/**
	 * Initial page / overview
	 *
	 * @return void
	 */
	public function index() {
		$id = $this->Translation->currentProjectId();

		$translateLocalesTable = $this->fetchTable('Translate.TranslateLocales');
		$languages = $translateLocalesTable->find('all')
			->where(['translate_project_id' => $id])
			->toArray();

		$count = $id ? $this->TranslateDomains->statistics($id, $languages) : 0;
		$coverage = $this->TranslateDomains->TranslateStrings->coverage($id);
		$projectSwitchArray = $this->TranslateDomains->TranslateProjects->find('list')
			->where(['TranslateProjects.status' => TranslateProject::STATUS_PUBLIC])
			->toArray();
		if (!$projectSwitchArray) {
			throw new NotFoundException('No public projects');
		}

		$this->set(compact('coverage', 'languages', 'count', 'projectSwitchArray'));
	}

	/**
	 * List all translation terms with filtering
	 *
	 * @return void
	 */
	public function terms() {
		$projectId = $this->Translation->currentProjectId();
		$translateStringsTable = $this->fetchTable('Translate.TranslateStrings');

		$query = $translateStringsTable->find('search', search: $this->request->getQuery());
		$query->contain([
			'TranslateDomains',
			'TranslateTerms' => ['TranslateLocales'],
		])->innerJoinWith('TranslateDomains', function ($q) use ($projectId) {
			return $q->where(['TranslateDomains.translate_project_id' => $projectId]);
		});

		$translateStrings = $this->paginate($query);

		// Get filter options
		$translateDomains = $translateStringsTable->TranslateDomains
			->find('list')
			->where(['translate_project_id' => $projectId])
			->orderBy(['name' => 'ASC'])
			->toArray();

		$translateLocales = $translateStringsTable->TranslateTerms->TranslateLocales
			->find('list', ['keyField' => 'id', 'valueField' => 'name'])
			->where(['translate_project_id' => $projectId])
			->orderBy(['name' => 'ASC'])
			->toArray();

		$this->set(compact('translateStrings', 'translateDomains', 'translateLocales'));
	}

	/**
	 * @param string|null $domain
	 * @param int|null $id
	 * @return \Cake\Http\Response|null|void
	 */
	public function translate(?string $domain = null, ?int $id = null) {
		$translateStringsTable = $this->fetchTable('Translate.TranslateStrings');
		$projectId = $this->Translation->currentProjectId();

		if (!$domain) {
			/** @var \Translate\Model\Entity\TranslateString|null $next */
			$next = $translateStringsTable->getNext(null, null)->contain(['TranslateDomains'])->first();
			if ($next) {
				return $this->redirect([$next->translate_domain->name, $next->id]);
			}

			$this->Flash->warning('No more open translations');

			return $this->redirect(['action' => 'index']);
		}

		$translateString = $translateStringsTable->get($id, ['contain' => ['TranslateDomains' => 'TranslateProjects']]);

		/** @var \Translate\Model\Entity\TranslateLocale[] $translateLocales */
		$translateLocales = $translateStringsTable->TranslateTerms->TranslateLocales->find()
			->where(['translate_project_id' => $projectId])
			->all()
			->toArray();

		// Get all domains for current project with statistics
		$domainsTable = $this->fetchTable('Translate.TranslateDomains');
		$domains = $domainsTable->find()
			->where(['translate_project_id' => $projectId, 'active' => true])
			->orderBy(['name' => 'ASC'])
			->all()
			->toArray();

		$domainStats = [];
		foreach ($domains as $domainEntity) {
			$totalStrings = $translateStringsTable->find()
				->where(['translate_domain_id' => $domainEntity->id])
				->count();

			$translatedStrings = $translateStringsTable->find()
				->where(['translate_domain_id' => $domainEntity->id])
				->matching('TranslateTerms', function ($q) {
					return $q->where(['TranslateTerms.content IS NOT' => null]);
				})
				->count();

			$domainStats[$domainEntity->id] = [
				'name' => $domainEntity->name,
				'total' => $totalStrings,
				'translated' => $translatedStrings,
				'percentage' => $totalStrings > 0 ? (int)(($translatedStrings / $totalStrings) * 100) : 0,
			];
		}
		if (!$translateLocales) {
			$this->Flash->error(__d('translate', 'You need at least one language to translate'));

			return $this->redirect(['controller' => 'TranslateLocales', 'action' => 'add']);
		}

		$translateTerms = $translateStringsTable->TranslateTerms->getTranslatedArray($id);
		if ($this->Translation->isPosted()) {
			if ($this->request->getData('skip')) {
				$translateString->skipped = true;
				$translateStringsTable->saveOrFail($translateString);

				// Try to find next in same domain first
				$next = $translateStringsTable->getNext($translateString->translate_domain_id, null)
					->contain(['TranslateDomains'])
					->where(['TranslateStrings.id >' => $id])
					->orderBy(['TranslateStrings.id' => 'ASC'])
					->first();

				// If no more in this domain, try any domain
				if (!$next) {
					$next = $translateStringsTable->getNext(null, null)
						->contain(['TranslateDomains'])
						->where(['TranslateStrings.id >' => $id])
						->orderBy(['TranslateStrings.id' => 'ASC'])
						->first();
				}

				if ($next) {
					return $this->redirect(['action' => 'translate', $next->translate_domain->name, $next->id]);
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
						$translateTerm = $translateStringsTable->TranslateTerms->newEmptyEntity();
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

					$translateTerm = $translateStringsTable->TranslateTerms->patchEntity($translateTerm, $data);
					if (!$translateStringsTable->TranslateTerms->save($translateTerm)) {
						$translateString->setError('content_' . $key, $translateTerm->getError('content'));
						$success = false;
					}
				}
			}

			if ($success) {
				if (array_key_exists('next', $this->request->getData())) {
					// Try to find next in same domain first
					$next = $translateStringsTable->getNext($translateString->translate_domain_id, null)
						->contain(['TranslateDomains'])
						->where(['TranslateStrings.id >' => $id])
						->orderBy(['TranslateStrings.id' => 'ASC'])
						->first();

					// If no more in this domain, try any domain
					if (!$next) {
						$next = $translateStringsTable->getNext(null, null)
							->contain(['TranslateDomains'])
							->where(['TranslateStrings.id >' => $id])
							->orderBy(['TranslateStrings.id' => 'ASC'])
							->first();
					}

					if ($next) {
						return $this->redirect(['action' => 'translate', $next->translate_domain->name, $next->id]);
					}

					$this->Flash->success(__d('translate', 'No more open translations.'));

					return $this->redirect(['action' => 'index']);
				}

				$this->Flash->success(__d('translate', 'Translation saved successfully.'));

				return $this->redirect(['action' => 'translate', $translateString->translate_domain->name, $id]);
			}
		} else {
			foreach ($translateTerms as $translateTerm) {
				$key = $translateStringsTable->resolveLanguageKey($translateTerm->translate_locale_id, $translateLocales);
				$this->request = $this->request->withData('content_' . $key, $translateTerm->content);
				$this->request = $this->request->withData('plural_2_' . $key, $translateTerm->plural_2);
			}
		}

		$suggestions = $translateStringsTable->getSuggestions($translateString, $translateLocales, $translateTerms);

		$this->set(compact('translateString', 'suggestions', 'translateLocales', 'domainStats'));
	}

	/**
	 * @param int $id
	 * @param int $reference
	 * @return void
	 */
	public function displayReference(int $id, int $reference) {
		$this->viewBuilder()->setLayout('ajax');

		$translateString = $this->fetchTable('Translate.TranslateStrings')->get($id, ['contain' => ['TranslateDomains']]);

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

		$reference = $occ[$reference];
		[$reference, $lines] = explode(':', $reference);
		$lines = explode(';', $lines);

		$path = $translateString->translate_domain->translate_project->path ?? null;
		if (!$path) {
			$path = ROOT;
		} elseif (!str_starts_with($path, '/')) {
			$path = ROOT . DS . $path;
		}
		$path = rtrim((string)realpath($path), '/') . '/';
		if (!is_dir($path)) {
			throw new NotFoundException('Path not found: ' . ($translateString->translate_domain->translate_project->path ?? 'ROOT'));
		}

		$file = $path . $reference;
		if (!file_exists($file)) {
			throw new NotFoundException('File not found: ' . $file);
		}

		$fileArray = file($file);

		$this->set(compact('fileArray', 'lines', 'reference'));
	}

	/**
	 * Switch the application language/locale
	 *
	 * @return \Cake\Http\Response
	 */
	public function switchLanguage() {
		$locale = $this->request->getQuery('locale');
		if (!$locale) {
			$this->Flash->error(__d('translate', 'Invalid locale'));

			return $this->redirect(['action' => 'index']);
		}

		// Validate locale exists and is active
		$language = $this->fetchTable('Translate.TranslateLocales')
			->find()
			->where([
				'locale' => $locale,
				'active' => true,
			])
			->first();

		if (!$language) {
			$this->Flash->error(__d('translate', 'Language not found or not active'));

			return $this->redirect(['action' => 'index']);
		}

		// Store locale in session or cookie
		$this->request->getSession()->write('Config.language', $locale);

		$this->Flash->success(__d('translate', 'Language switched to {0}', $language->name));

		// Redirect back to previous page or index
		$redirect = $this->request->getQuery('redirect') ?: ['action' => 'index'];

		return $this->redirect($redirect);
	}

	/**
	 * Switch the current translation project
	 *
	 * @return \Cake\Http\Response
	 */
	public function switchProject() {
		$projectId = (int)$this->request->getData('project_switch');
		$translateProjectsTable = $this->fetchTable('Translate.TranslateProjects');
		$translateProject = $translateProjectsTable->get($projectId);

		$this->request->getSession()->write('TranslateProject.id', $translateProject->id);
		$this->Flash->success(__d('translate', 'Project switched to {0}', $translateProject->name));

		return $this->redirect(['action' => 'index']);
	}

}
