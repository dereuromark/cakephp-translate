<?php
namespace Translate\Controller\Admin;

use Cake\Core\Configure;
use Sepia\FileHandler;
use Sepia\PoParser;
use Translate\Controller\TranslateAppController;
use Translate\Lib\TranslationLib;

/**
 * TranslateStrings Controller
 *
 * @property \Translate\Model\Table\TranslateStringsTable $TranslateStrings
 */
class TranslateStringsController extends TranslateAppController {

	/**
	 * @var array
	 */
	public $paginate = ['order' => ['TranslateStrings.modified' => 'DESC']];

	/**
	 * @return void
	 */
	public function initialize() {
		parent::initialize();
		$this->loadComponent('Search.Prg', [
			'actions' => ['index'],
			'emptyValues' => [
				'missing_translation' => 0,
			],
		]);
	}

	/**
	 * Index method
	 *
	 * @return \Cake\Http\Response|null
	 */
	public function index() {
		$this->paginate = [
			'contain' => ['Users']
		];

		$query = $this->TranslateStrings->find('search', ['search' => $this->request->query]);
		$translateStrings = $this->paginate($query);

		$options = ['conditions' => ['translate_project_id' => $this->Translation->currentProjectId()]];
		$translateGroups = $this->TranslateStrings->getRelatedInUse('TranslateGroups', 'translate_group_id', 'list', $options);
		$this->set(compact('translateStrings', 'translateGroups'));
		$this->set('_serialize', ['translateStrings']);
	}

	/**
	 * View method
	 *
	 * @param string|null $id Translate String id.
	 * @return \Cake\Http\Response|null
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 */
	public function view($id = null) {
		$translateString = $this->TranslateStrings->get($id, [
			'contain' => ['Users', 'TranslateGroups', 'TranslateTerms']
		]);

		$this->set(compact('translateString'));
		$this->set('_serialize', ['translateString']);
	}

	/**
	 * Add method
	 *
	 * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
	 */
	public function add() {
		$translateString = $this->TranslateStrings->newEntity();
		if ($this->request->is('post')) {
			$translateString = $this->TranslateStrings->patchEntity($translateString, $this->request->data);
			if ($this->TranslateStrings->save($translateString)) {
				$this->Flash->success(__('The translate string has been saved.'));

				if ($this->request->data('translate_afterwards')) {
					return $this->redirect(['action' => 'translate', $translateString->id]);
				}
				return $this->redirect(['action' => 'index']);
			}

			$this->Flash->error(__('The translate string could not be saved. Please, try again.'));
		}
		$translateGroups = $this->TranslateStrings->TranslateGroups->find('list', ['limit' => 200]);

		$this->set(compact('translateString', 'translateGroups'));
		$this->set('_serialize', ['translateString']);
	}

	/**
	 * Edit method
	 *
	 * @param string|null $id Translate String id.
	 * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
	 * @throws \Cake\Network\Exception\NotFoundException When record not found.
	 */
	public function edit($id = null) {
		$translateString = $this->TranslateStrings->get($id, [
			'contain' => ['TranslateGroups']
		]);
		if ($this->request->is(['patch', 'post', 'put'])) {
			$translateString = $this->TranslateStrings->patchEntity($translateString, $this->request->data);
			if ($this->TranslateStrings->save($translateString)) {
				$this->Flash->success(__('The translate string has been saved.'));

				if ($this->request->data('translate_afterwards')) {
					return $this->redirect(['action' => 'translate', $id]);
				}

				return $this->redirect(['action' => 'index']);
			}

			$this->Flash->error(__('The translate string could not be saved. Please, try again.'));
		} else {
			$this->request->data = $this->request->query;
		}

		$translateGroups = $this->TranslateStrings->TranslateGroups->find('list', ['limit' => 200]);

		$this->set(compact('translateString', 'translateGroups'));
		$this->set('_serialize', ['translateString']);
	}

	/**
	 * Delete method
	 *
	 * @param string|null $id Translate String id.
	 * @return \Cake\Http\Response|null Redirects to index.
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 */
	public function delete($id = null) {
		$this->request->allowMethod(['post', 'delete']);
		$translateString = $this->TranslateStrings->get($id);
		if ($this->TranslateStrings->delete($translateString)) {
			$this->Flash->success(__('The translate string has been deleted.'));
		} else {
			$this->Flash->error(__('The translate string could not be deleted. Please, try again.'));
		}
		return $this->redirect(['action' => 'index']);
	}

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

			foreach ((array)$this->request->data['sel_pot'] as $key => $domain) {
				if (!$domain) {
					continue;
				}
				if (!in_array($domain, $potFiles)) {
					continue;
				}
				$translations = $translationLib->extractPotFile($domain);

				$translationGroup = $this->TranslateStrings->TranslateGroups->getGroup($this->Translation->currentProjectId(), $domain);

				foreach ($translations as $translation) {
					$success = (bool)$this->TranslateStrings->import($translation, $translationGroup->id);
					if (!$success) {
						$errors[] = '`' . h($translation['name']) . '`';
						continue;
					}
					$count += (int)$success;
				}
			}

			foreach ((array)$this->request->data['sel_po'] as $key => $domain) {
				if (!$domain) {
					continue;
				}
				list($lang, $domain) = explode('_', $domain, 2);
				if (!isset($poFiles[$lang][$lang . '_' . $domain])) {
					continue;
				}
				if (!isset($translateLanguages[$lang])) {
					continue;
				}

				$translations = $translationLib->extractPoFile($domain, $lang);

				$translationGroup = $this->TranslateStrings->TranslateGroups->getGroup($this->Translation->currentProjectId(), $domain);

				foreach ($translations as $name => $translation) {
					$translationString = $this->TranslateStrings->import($translation, $translationGroup->id);
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
			foreach ($potFiles as $key => $val) {
				$this->request->data['sel_pot'][] = $val;
			}
			foreach ($poFiles as $lang => $val) {
				if (!isset($translateLanguages[$lang])) {
					continue;
				}
				foreach ($val as $k => $v) {
					$this->request->data['sel_po'][] = $k;
				}
			}
		}

		$this->set(compact('potFiles', 'poFiles'));
	}

	/**
	 * @return void
	 */
	public function dump() {
		$translateLanguages = $this->TranslateStrings->TranslateTerms->TranslateLanguages->getExtractableAsList($this->Translation->currentProjectId());
		$domains = $this->TranslateStrings->TranslateGroups->getActive();

		$map = [];
		foreach ($translateLanguages as $code => $id) {
			foreach ($domains as $domain) {
				$map[$code][$code . '_' . $domain->name] = $domain->name;
			}
		}

		if ($this->Common->isPosted()) {
			$count = 0;
			$errors = [];

			foreach ((array)$this->request->data['domains'] as $key => $domain) {
				list($lang, $domain) = explode('_', $domain, 2);

				$langId = $this->TranslateStrings->TranslateTerms->TranslateLanguages->find()->where(['iso2' => $lang])->firstOrFail()->id;
				$groupId = $this->TranslateStrings->TranslateGroups->find()->where(['name' => $domain, 'translate_project_id' => $this->Translation->currentProjectId()])->firstOrFail()->id;
				$translations = $this->TranslateStrings->TranslateTerms->getTranslations($langId, $groupId)->toArray();

				if (!$translations) {
					continue;
				}

				if (!$this->_dump($translations, $domain, $lang)) {
					$errors[] = $lang . '/' . $domain;
					continue;
				}

				$count++;
			}

			$this->Flash->success('Done: ' . $count . ' files');
			if ($errors) {
				$this->Flash->error(count($errors) . ' errors: ' . implode(', ', $errors));
			}
			//$this->redirect(array('action'=>'index'));

		} else {
			foreach ($translateLanguages as $code => $id) {
				foreach ($domains as $domain) {
					$this->request->data['domains'][] = $code . '_' . $domain->name;
				}
			}
		}

		$this->set(compact('map'));
	}

	/**
	 * Main translation view
	 * - all needed language boxes
	 *
	 * @param int|null $id
	 *
	 * @return \Cake\Http\Response|null
	 */
	public function translate($id = null) {
		$translateString = $this->TranslateStrings->get($id, ['contain' => 'TranslateGroups']);

		$translateLanguages = $this->TranslateStrings->TranslateTerms->TranslateLanguages->find('all');

		if ($translateLanguages->count() < 1) {
			$this->Flash->error(__d('translate', 'You need at least one language to translate'));
			return $this->redirect(['controller' => 'TranslateLanguages', 'action' => 'add']);
		}

		$translateTerms = $this->TranslateStrings->TranslateTerms->getTranslatedArray($id);

		if ($this->Common->isPosted()) {
			$success = true;
			foreach ($translateLanguages as $translateLanguage) {
				$key = $translateLanguage->iso2;
				if (isset($this->request->data['content_' . $key])) {
					$term = $this->request->data['content_' . $key];
					if (!isset($translateTerms[$translateLanguage->id])) {
						$translateTerm = $this->TranslateStrings->TranslateTerms->newEntity();
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
						$data['plural_2'] = $this->request->data['plural_2_' . $key];
					}

					$translateTerm = $this->TranslateStrings->TranslateTerms->patchEntity($translateTerm, $data);
					if (!$this->TranslateStrings->TranslateTerms->save($translateTerm)) {
						$translateString->setError('content_' . $key, $translateTerm->getError('content'));
						$success = false;
					}
				}
			}

			if ($success) {
				if (array_key_exists('next', $this->request->data)) {
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
				$this->request->data['content_' . $key] = $translateTerm->content;
				$this->request->data['plural_2_' . $key] = $translateTerm->plural_2;
			}
		}

		$this->set(compact('translateString', 'translateLanguages'));
	}

	/**
	 * @param \Translate\Model\Entity\TranslateTerm[] $translations
	 * @param string $domain
	 * @param string $lang
	 *
	 * @return bool
	 */
	protected function _dump(array $translations, $domain, $lang) {
		$folder = LOCALE . $lang . DS;
		if (!is_dir($folder)) {
			mkdir($folder, 0770, true);
		}
		$file = $folder . $domain . '.po';
		if (!file_exists($file)) {
			touch($file);
		}

		$max = Configure::read('Translate.plurals') ?: 2;
		$pluralExpression = Configure::read('Translate.pluralExpression') ?: 'n != 1';

		$po = new PoParser(new FileHandler($file));
		$newHeaders = [
			'"Project-Id-Version: \n"',
			'"POT-Creation-Date: \n"',
			'"PO-Revision-Date: \n"',
			'"Last-Translator: none\n"',
			'"Language-Team: \n"',
			'"MIME-Version: 1.0\n"',
			'"Content-Type: text/plain; charset=utf-8\n"',
			'"Content-Transfer-Encoding: 8bit\n"',
			'"Plural-Forms: nplurals=' . $max . '; plural=' . $pluralExpression . ';\n"'
		];

		$po->setHeaders($newHeaders);

		foreach ($translations as $translation) {
			$entry = [
				'msgid' => $translation->translate_string->name,
				'msgstr' => (string)$translation->content,
			];
			if ($translation->translate_string->plural !== null) {
				$entry['msgid_plural'] = $translation->translate_string->plural;
				$entry['msgstr[0]'] = (array)$entry['msgstr'];
				for ($i = 2; $i <= $max; $i++) {
					$pluralVersion = 'plural_' . $i;
					$entry['msgstr[' . ($i - 1) . ']'] = (array)(string)$translation->get($pluralVersion);
				}
			}

			if ($translation->translate_string->flags) {
				//$entry['flags'] = explode(',', $translation->translate_string->flags);
			}

			$po->setEntry($translation->translate_string->name, $entry);
			if ($translation->translate_string->plural !== null) {
				//$po->setEntryPlural($translation->translate_string->name, $entry['msgstr']);
			}
		}

		$content = $po->compile();

		return (bool)file_put_contents($file, $content);
	}

}
