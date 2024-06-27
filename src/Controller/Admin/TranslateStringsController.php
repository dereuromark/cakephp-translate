<?php

namespace Translate\Controller\Admin;

use Cake\Http\Exception\NotFoundException;
use Translate\Controller\TranslateAppController;
use Translate\Filesystem\Dumper;
use Translate\Lib\TranslationLib;

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
		$query = $this->TranslateStrings->find('search', ...['search' => $this->request->getQuery()]);
		$query->contain([
			'TranslateDomains',
		]);
		$translateStrings = $this->paginate($query);

		$options = ['conditions' => ['translate_project_id' => $this->Translation->currentProjectId()]];
		$translateDomains = $this->TranslateStrings->getRelatedInUse('TranslateDomains', 'translate_domain_id', 'list', $options);
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
			'contain' => ['TranslateDomains', 'TranslateTerms'],
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
		if ($this->request->is(['patch', 'post', 'put'])) {
			$translateString = $this->TranslateStrings->patchEntity($translateString, $this->request->getData());
			if ($this->TranslateStrings->save($translateString)) {
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
		//$sourceContent = $this->Common->showSource(__FILE__, true);
		//$sourceContent = show_source(__FILE__, true);
		$sourceFile = __FILE__;
		$this->set(compact('sourceFile'));
	}

	/**
	 * @return void
	 */
	public function extract() {
		$translationLib = new TranslationLib();
		$potFiles = $translationLib->getPotFiles();
		$translateLanguages = $this->TranslateStrings->TranslateTerms->TranslateLanguages->getExtractableAsList($this->Translation->currentProjectId());
		$poFileLanguages = $translationLib->getPoFileLanguages();
		$poFiles = [];
		foreach ($poFileLanguages as $poFileLanguage) {
			$poFiles[$poFileLanguage] = $translationLib->getPoFiles($poFileLanguage);
		}

		if ($this->Common->isPosted()) {
			$count = 0;
			$errors = [];

			foreach ((array)$this->request->getData('sel_pot') as $key => $domain) {
				if (!$domain) {
					continue;
				}
				if (!in_array($domain, $potFiles, true)) {
					continue;
				}
				$translations = $translationLib->extractPotFile($domain);

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
				if (!isset($translateLanguages[$lang])) {
					continue;
				}
				$translations = $translationLib->extractPoFile($domain, $locale);

				$translationDomain = $this->TranslateStrings->TranslateDomains->getDomain($this->Translation->currentProjectId(), $domain);

				foreach ($translations as $name => $translation) {
					$translationString = $this->TranslateStrings->import($translation, $translationDomain->id);
					if (!$translationString) {
						$errors[] = '`' . h($translation['name']) . '`';

						continue;
					}

					$success = (bool)$this->TranslateStrings->TranslateTerms->import($translation, $translationString->id, $translateLanguages[$lang]);
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
				if (!isset($translateLanguages[$locale])) {
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
		$translateLanguages = $this->TranslateStrings->TranslateTerms->TranslateLanguages->getExtractableAsList($this->Translation->currentProjectId());
		$domains = $this->TranslateStrings->TranslateDomains->getActive();

		$map = [];
		foreach ($translateLanguages as $code => $id) {
			foreach ($domains as $domain) {
				$map[$code][$code . '_' . $domain->name] = $domain->name;
			}
		}

		if ($this->Common->isPosted() && $this->request->getData('domains')) {
			$count = 0;
			$errors = [];
			/** @var array<string> $postedDomains */
			$postedDomains = (array)$this->request->getData('domains');
			foreach ($postedDomains as $key => $domain) {
				[$lang, $domain] = explode('_', $domain, 2);

				$langId = $this->TranslateStrings->TranslateTerms->TranslateLanguages->find()->where(['iso2' => $lang])->firstOrFail()->id;
				$groupId = $this->TranslateStrings->TranslateDomains->find()->where(['name' => $domain, 'translate_project_id' => $this->Translation->currentProjectId()])->firstOrFail()->id;
				$translations = $this->TranslateStrings->TranslateTerms->getTranslations($langId, $groupId)->toArray();

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

		} elseif ($this->Common->isPosted() && !$map) {
			$this->Flash->warning('Please activate a domain for dumping.');

			return $this->redirect(['controller' => 'TranslateDomains', 'action' => 'index']);
		} elseif (!$this->Common->isPosted()) {
			$domainArray = [];
			foreach ($translateLanguages as $code => $id) {
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
		$translateString = $this->TranslateStrings->get($id, ['contain' => 'TranslateDomains']);

		$translateLanguages = $this->TranslateStrings->TranslateTerms->TranslateLanguages->find()->all()->toArray();
		if (!$translateLanguages) {
			$this->Flash->error(__d('translate', 'You need at least one language to translate'));

			return $this->redirect(['controller' => 'TranslateLanguages', 'action' => 'add']);
		}

		$translateTerms = $this->TranslateStrings->TranslateTerms->getTranslatedArray($id);

		if ($this->Common->isPosted()) {
			$success = true;
			foreach ($translateLanguages as $translateLanguage) {
				$key = $translateLanguage->iso2;
				$term = $this->request->getData('content_' . $key);
				if ($term !== null) {
					if (!isset($translateTerms[$translateLanguage->id])) {
						$translateTerm = $this->TranslateStrings->TranslateTerms->newEmptyEntity();
					} else {
						$translateTerm = $translateTerms[$translateLanguage->id];
					}

					$data = [
						'translate_string_id' => $id,
						'translate_language_id' => $translateLanguage->id,
						'content' => $term,
						//'user_id' => $this->AuthUser->id()
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
					$next = $this->TranslateStrings->getNext($id)->first();
					if (!empty($next['id'])) {
						return $this->redirect([$next['id']]);
					}
				}

				return $this->redirect(['action' => 'index']);
			}
		} else {
			foreach ($translateTerms as $translateTerm) {
				$key = $this->TranslateStrings->resolveLanguageKey($translateTerm->translate_language_id, $translateLanguages);

				$this->request = $this->request->withData('content_' . $key, $translateTerm->content);
				$this->request = $this->request->withData('plural_2_' . $key, $translateTerm->plural_2);
			}
		}

		$suggestions = $this->TranslateStrings->getSuggestions($translateString, $translateLanguages, $translateTerms);
		//$pluralSuggestions =

		$this->set(compact('translateString', 'translateLanguages', 'suggestions'));
	}

	/**
	 * @param int $id
	 * @param int $reference 0 based
	 * @throws \Cake\Http\Exception\NotFoundException
	 * @return void
	 */
	public function displayReference($id, $reference) {
		$translateString = $this->TranslateStrings->get($id, ['contain' => ['TranslateDomains']]);

		$sep = explode(PHP_EOL, $translateString['references']);
		$occ = [];
		foreach ($sep as $s) {
			$s = trim($s);
			if ($s !== '') {
				$occ[] = $s;
			}
		}
		if (!isset($occ[(int)$reference])) {
			throw new NotFoundException('Could not find reference `' . $reference . '`');
		}

		$reference = $occ[(int)$reference];
		[$reference, $lines] = explode(':', $reference);
		$lines = explode(';', $lines);

		$path = $translateString->translate_domain->path;
		if (substr($path, 0, 1) !== '/') {
			$path = ROOT . DS . $path;
		}
		$path = rtrim((string)realpath($path), '/') . '/';
		if (!is_dir($path)) {
			throw new NotFoundException('Path not found: ' . $translateString->translate_domain->path);
		}

		$file = $path . $reference;
		if (!file_exists($file)) {
			throw new NotFoundException('File not found: ' . $file);
		}

		$fileArray = file($file);

		$this->set(compact('fileArray', 'lines'));
	}

	/**
	 * Import from blob or other source/file
	 *
	 * @return void
	 */
	public function import() {
		if ($this->Common->isPosted()) {

		}
	}

}
