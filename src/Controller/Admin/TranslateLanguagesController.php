<?php
namespace Translate\Controller\Admin;

use Translate\Controller\TranslateAppController;
use Translate\Filesystem\Creator;

/**
 * TranslateLanguages Controller
 *
 * @property \Translate\Model\Table\TranslateLanguagesTable $TranslateLanguages
 * @method \Translate\Model\Entity\TranslateLanguage[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class TranslateLanguagesController extends TranslateAppController {

	/**
	 * @var array
	 */
	public $paginate = ['order' => ['TranslateLanguages.name' => 'ASC']];

	/**
	 * @return \Cake\Http\Response|null
	 */
	public function toLocale() {
		$path = LOCALE;

		$creator = new Creator();
		$existingFolders = $creator->findLocaleFolders($path);
		$languages = $this->TranslateLanguages->find('list', ['keyField' => 'locale'])->toArray();

		if ($this->Common->isPosted()) {
			$data = [];
			foreach ($this->request->data['locale'] as $lang => $value) {
				if (!empty($value)) {
					$data[] = $lang;
				}
			}

			if (!empty($data) && $creator->createLocaleFolders($data, $path) === true) {
				$this->Flash->success('New locale folders created');
				return $this->redirect(['action' => 'index']);
			}

			$this->Flash->error('Sth went wrong,');
		} else {
			foreach ($languages as $k => $v) {
				$this->request->data['locale'][$k] = true;
			}
		}

		$this->set(compact('path', 'languages', 'existingFolders'));
	}

	/**
	 * @return \Cake\Http\Response|null
	 */
	public function fromLocale() {
		$path = LOCALE;

		$creator = new Creator();
		$folders = $creator->findLocaleFolders($path);
		$existingLanguages = $this->TranslateLanguages->find('list', ['keyField' => 'locale'])->toArray();

		if ($this->Common->isPosted()) {
			$translateLanguages = [];
			foreach ($this->request->data['language'] as $key => $data) {
				if (empty($data['confirm'])) {
					continue;
				}

				$data = [
					'locale' => $key,
					'name' => $data['name'],
					//'active' => true,
					'translate_project_id' => $this->Translation->currentProjectId(),
				];
				if (strlen($data['locale']) === 2) {
					$data['iso2'] = $data['locale'];
				} elseif (preg_match('/[a-z]{2}_[a-z]{2}/i', $data['locale'])) {
					$data['iso2'] = substr($data['locale'], 0, 2);
				}

				$translateLanguages[] = $this->TranslateLanguages->newEntity($data);
			}

			if (!empty($data) && $this->TranslateLanguages->saveMany($translateLanguages)) {
				$this->Flash->success('new language(s) added');
				return $this->redirect(['action' => 'index']);
			}

			$this->Flash->error('Sth went wrong.');

		}
		$this->set(compact('path', 'existingLanguages', 'folders'));
	}

	/**
	 * Index method
	 *
	 * @return \Cake\Http\Response|null
	 */
	public function index() {
		$translateLanguages = $this->paginate();

		$this->set(compact('translateLanguages'));
		$this->set('_serialize', ['translateLanguages']);
	}

	/**
	 * View method
	 *
	 * @param string|null $id Translate Language id.
	 * @return \Cake\Http\Response|null
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 */
	public function view($id = null) {
		$translateLanguage = $this->TranslateLanguages->get($id, [
			'contain' => ['TranslateTerms']
		]);

		$this->set(compact('translateLanguage'));
		$this->set('_serialize', ['translateLanguage']);
	}

	/**
	 * Add method
	 *
	 * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
	 */
	public function add() {
		$translateLanguage = $this->TranslateLanguages->newEntity();
		if ($this->request->is('post')) {
			$this->request->data['translate_project_id'] = $this->Translation->currentProjectId();

			$translateLanguage = $this->TranslateLanguages->patchEntity($translateLanguage, $this->request->data);
			if ($this->TranslateLanguages->save($translateLanguage)) {
				$this->Flash->success(__d('translate', 'The translate language has been saved.'));
				return $this->redirect(['action' => 'index']);
			}

			$this->Flash->error(__d('translate', 'The translate language could not be saved. Please, try again.'));
		}

		$this->set(compact('translateLanguage'));
		$this->set('_serialize', ['translateLanguage']);
	}

	/**
	 * Edit method
	 *
	 * @param string|null $id Translate Language id.
	 * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
	 * @throws \Cake\Network\Exception\NotFoundException When record not found.
	 */
	public function edit($id = null) {
		$translateLanguage = $this->TranslateLanguages->get($id, [
			'contain' => []
		]);
		if ($this->request->is(['patch', 'post', 'put'])) {
			$translateLanguage = $this->TranslateLanguages->patchEntity($translateLanguage, $this->request->data);
			if ($this->TranslateLanguages->save($translateLanguage)) {
				$this->Flash->success(__d('translate', 'The translate language has been saved.'));
				return $this->redirect(['action' => 'index']);
			}

			$this->Flash->error(__d('translate', 'The translate language could not be saved. Please, try again.'));
		}

		$this->set(compact('translateLanguage'));
		$this->set('_serialize', ['translateLanguage']);
	}

	/**
	 * Delete method
	 *
	 * @param string|null $id Translate Language id.
	 * @return \Cake\Http\Response|null Redirects to index.
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 */
	public function delete($id = null) {
		$this->request->allowMethod(['post', 'delete']);
		$translateLanguage = $this->TranslateLanguages->get($id);
		if ($this->TranslateLanguages->delete($translateLanguage)) {
			$this->Flash->success(__d('translate', 'The translate language has been deleted.'));
		} else {
			$this->Flash->error(__d('translate', 'The translate language could not be deleted. Please, try again.'));
		}
		return $this->redirect(['action' => 'index']);
	}

}
