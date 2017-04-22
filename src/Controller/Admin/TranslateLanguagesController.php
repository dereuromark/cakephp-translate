<?php
namespace Translate\Controller\Admin;

use Cake\Filesystem\Folder;
use Translate\Controller\TranslateAppController;

/**
 * TranslateLanguages Controller
 *
 * @property \Translate\Model\Table\TranslateLanguagesTable $TranslateLanguages
 */
class TranslateLanguagesController extends TranslateAppController {

	/**
	 * @var array
	 */
	public $paginate = ['order' => ['TranslateLanguages.name' => 'ASC']];

	/**
	 * @return void
	 */
	public function toLocale() {
		$existingFolders = $this->_findLocaleFolders();
		$languages = $this->TranslateLanguages->find('list');

		if ($this->Common->isPosted()) {
			$data = [];
			foreach ($this->request->data as $lang) {
				if (!empty($lang['confirm'])) {
					$data[] = $lang['folder'];
				}
			}
			if (!empty($data) && $this->_createLocaleFolders($data) === true) {
				$this->Flash->success('new locale folders created');
				//$this->redirect(array('action'=>'index'));
			} else {
				$this->Flash->error('Sth went wrong');
			}
		}

		$this->set(compact('languages', 'existingFolders'));
	}

	/**
	 * @return \Cake\Http\Response|null
	 */
	public function fromLocale() {
		$folders = $this->_findLocaleFolders();
		$existingLanguages = $this->TranslateLanguages->find('list');

		if ($this->Common->isPosted()) {
			$data = $this->request->data;
			foreach ($data as $key => $value) {
				if (empty($value['confirm'])) {
					unset($data[$key]);
				} else {
					$data[$key]['active'] = 1;
				}
			}

			$translateLanguage = $this->TranslateLanguages->newEntity($this->request->data);

			if (!empty($data) && $this->TranslateLanguages->saveAll($data, ['validate' => 'first', 'fieldList' => ['name', 'locale']])) {
				$this->Flash->success('new language(s) added');
				return $this->redirect(['action' => 'index']);
			}

		}
		$this->set(compact('existingLanguages', 'folders'));
	}

	/**
	 * @return array folders;
	 */
	public function _findLocaleFolders() {
		$handle = new Folder(LOCALE);
		$folders = $handle->read(true, true);
		return $folders[0];
	}

	/**
	 * @param array $folders
	 *
	 * @return mixed Bool TRUE on sucess, array $errors on failure
	 */
	public function _createLocaleFolders($folders = []) {
		$basepath = LOCALE;
		$handle = new Folder($basepath, true);
		if ($handle->errors()) {
			return $handle->errors();
		}
		foreach ($folders as $folder) {
			if (!$handle->create($basepath . $folder . DS) || !$handle->create($basepath . $folder . DS . 'LC_MESSAGES' . DS)) {
				return $handle->errors();
			}
		}
		return true;
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
