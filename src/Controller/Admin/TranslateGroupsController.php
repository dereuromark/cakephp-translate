<?php
namespace Translate\Controller\Admin;

use Translate\Controller\TranslateAppController;

/**
 * TranslateGroups Controller
 *
 * @property \Translate\Model\Table\TranslateGroupsTable $TranslateGroups
 */
class TranslateGroupsController extends TranslateAppController {

	/**
	 * @var array
	 */
	public $paginate = ['order' => ['TranslateGroups.modified' => 'DESC']];

	/**
	 * Extract from pot file, directly the code
	 *
	 * @return void
	 */
	public function extract() {
		$translationLib = new TranslationLib();
		$potFiles = $translationLib->getPotFiles();
		$poFiles = $translationLib->getPoFiles();

		if ($this->Common->isPosted()) {
			$count = 0;
			if (!empty($this->request->data['source_code'])) {
				//TODO: specify: app, cake, vendors or any subfolder of those
				$names = $translationLib->extractSourceCode();
				foreach ($names as $name => $occurances) {
					$count += $this->_add((array)$name, ['description' => 'Source Code']);
				}
			}

			if (!empty($this->request->data['controller_names'])) {

				$names = $translationLib->getResourceNames();
				$count += $this->_add($names, ['description' => 'Controller Name']);
			}

			foreach ((array)$this->request->data['sel_pot'] as $key => $val) {
				if (!in_array($val, $potFiles)) {
					continue;
				}
				$file = $val; //$potFiles[$key];
				$names = $translationLib->extractPotFile($file);
				foreach ($names as $name => $translation) {
					$count += $this->_add((array)$name, ['description' => 'Pot File']);
				}
			}

			foreach ((array)$this->request->data['sel_po'] as $key => $val) {
				if (!in_array($val, $poFiles)) {
					continue;
				}
				$file = $val; //$potFiles[$key];
				$names = $translationLib->extractPoFile($file);
				foreach ($names as $name => $translation) {
					$count += $this->_add((array)$name, ['description' => 'Po File']);
				}
			}

			$this->Flash->success('Done ' . $count);
			//$this->redirect(array('action'=>'index'));

		} else {
			foreach ($potFiles as $key => $val) {
				$this->request->data['sel_pot'][] = $val;
			}
			$this->request->data['controller_names'] = 1;
		}

		$this->set(compact('potFiles', 'poFiles'));
	}

	public function _add($names, $customData = []) {
		$count = 0;
		foreach ($names as $name) {
					$this->TranslateGroups->TranslateStrings->create();
					$data = array_merge([
						'name' => $name,
						'user_id' => '1',
						//'active' => 1
					], $customData);
					if ($this->TranslateGroups->TranslateStrings->save($data)) {
						$count++;
					}
				}
		return $count;
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

	/**
	 * Index method
	 *
	 * @return \Cake\Http\Response|null
	 */
	public function index() {
		$this->paginate = [
			'contain' => ['TranslateProjects']
		];
		$translateGroups = $this->paginate();

		$this->set(compact('translateGroups'));
		$this->set('_serialize', ['translateGroups']);
	}

	/**
	 * View method
	 *
	 * @param string|null $id Translate Group id.
	 * @return \Cake\Http\Response|null
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 */
	public function view($id = null) {
		$translateGroup = $this->TranslateGroups->get($id, [
			'contain' => ['TranslateProjects', 'TranslateStrings']
		]);

		$this->set(compact('translateGroup'));
		$this->set('_serialize', ['translateGroup']);
	}

	/**
	 * Add method
	 *
	 * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
	 */
	public function add() {
		$translateGroup = $this->TranslateGroups->newEntity();
		if ($this->request->is('post')) {
			$this->request->data['translate_project_id'] = $this->Translation->currentProjectId();

			$translateGroup = $this->TranslateGroups->patchEntity($translateGroup, $this->request->data);
			if ($this->TranslateGroups->save($translateGroup)) {
				$this->Flash->success(__d('translate', 'The translate group has been saved.'));
				return $this->redirect(['action' => 'index']);
			}

			$this->Flash->error(__d('translate', 'The translate group could not be saved. Please, try again.'));
		} else {
			$this->request->data['active'] = true;
		}

		$this->set(compact('translateGroup'));
	}

	/**
	 * Edit method
	 *
	 * @param string|null $id Translate Group id.
	 * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
	 * @throws \Cake\Network\Exception\NotFoundException When record not found.
	 */
	public function edit($id = null) {
		$translateGroup = $this->TranslateGroups->get($id, [
			'contain' => ['TranslateStrings']
		]);
		if ($this->request->is(['patch', 'post', 'put'])) {
			$translateGroup = $this->TranslateGroups->patchEntity($translateGroup, $this->request->data);
			if ($this->TranslateGroups->save($translateGroup)) {
				$this->Flash->success(__d('translate', 'The translate group has been saved.'));
				return $this->redirect(['action' => 'index']);
			}

			$this->Flash->error(__d('translate', 'The translate group could not be saved. Please, try again.'));
		}
		$translateProjects = $this->TranslateGroups->TranslateProjects->find('list', ['limit' => 200]);
		$translateStrings = $this->TranslateGroups->TranslateStrings->find('list', ['limit' => 200]);

		$this->set(compact('translateGroup', 'translateProjects', 'translateStrings'));
		$this->set('_serialize', ['translateGroup']);
	}

	/**
	 * Delete method
	 *
	 * @param string|null $id Translate Group id.
	 * @return \Cake\Http\Response|null Redirects to index.
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 */
	public function delete($id = null) {
		$this->request->allowMethod(['post', 'delete']);
		$translateGroup = $this->TranslateGroups->get($id);
		if ($this->TranslateGroups->delete($translateGroup)) {
			$this->Flash->success(__d('translate', 'The translate group has been deleted.'));
		} else {
			$this->Flash->error(__d('translate', 'The translate group could not be deleted. Please, try again.'));
		}
		return $this->redirect(['action' => 'index']);
	}

}
