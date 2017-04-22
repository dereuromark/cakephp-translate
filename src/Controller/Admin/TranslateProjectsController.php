<?php
namespace Translate\Controller\Admin;

use Translate\Controller\TranslateAppController;

/**
 * TranslateProjects Controller
 *
 * @property \Translate\Model\Table\TranslateProjectsTable $TranslateProjects
 */
class TranslateProjectsController extends TranslateAppController {

	/**
	 * Index method
	 *
	 * @return \Cake\Http\Response|null
	 */
	public function index() {
		$translateProjects = $this->paginate();

		$this->set(compact('translateProjects'));
		$this->set('_serialize', ['translateProjects']);
	}

	/**
	 * View method
	 *
	 * @param string|null $id Translate Project id.
	 * @return \Cake\Http\Response|null
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 */
	public function view($id = null) {
		$translateProject = $this->TranslateProjects->get($id, [
			'contain' => ['TranslateGroups']
		]);

		$this->set(compact('translateProject'));
		$this->set('_serialize', ['translateProject']);
	}

	/**
	 * Add method
	 *
	 * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
	 */
	public function add() {
		if ($this->TranslateProjects->find()->count() > 0) {
			$this->Flash->warning('Currently only one project is supported yet.');
			return $this->Common->autoRedirect(['action' => 'index']);
		}

		$translateProject = $this->TranslateProjects->newEntity();
		if ($this->request->is('post')) {
			$translateProject = $this->TranslateProjects->patchEntity($translateProject, $this->request->data);
			if ($this->TranslateProjects->save($translateProject)) {
				$this->Flash->success(__('The translate project has been saved.'));
				return $this->redirect(['action' => 'index']);
			}

			$this->Flash->error(__('The translate project could not be saved. Please, try again.'));
		}

		$this->set(compact('translateProject'));
		$this->set('_serialize', ['translateProject']);
	}

	/**
	 * Edit method
	 *
	 * @param string|null $id Translate Project id.
	 * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
	 * @throws \Cake\Network\Exception\NotFoundException When record not found.
	 */
	public function edit($id = null) {
		$translateProject = $this->TranslateProjects->get($id, [
			'contain' => []
		]);
		if ($this->request->is(['patch', 'post', 'put'])) {
			$translateProject = $this->TranslateProjects->patchEntity($translateProject, $this->request->data);
			if ($this->TranslateProjects->save($translateProject)) {
				$this->Flash->success(__('The translate project has been saved.'));
				return $this->redirect(['action' => 'index']);
			}

			$this->Flash->error(__('The translate project could not be saved. Please, try again.'));
		}

		$this->set(compact('translateProject'));
		$this->set('_serialize', ['translateProject']);
	}

	/**
	 * Delete method
	 *
	 * @param string|null $id Translate Project id.
	 * @return \Cake\Http\Response|null Redirects to index.
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 */
	public function delete($id = null) {
		$this->request->allowMethod(['post', 'delete']);
		$translateProject = $this->TranslateProjects->get($id);
		if ($this->TranslateProjects->delete($translateProject)) {
			$this->Flash->success(__('The translate project has been deleted.'));
		} else {
			$this->Flash->error(__('The translate project could not be deleted. Please, try again.'));
		}
		return $this->redirect(['action' => 'index']);
	}

	/**
	 * @return \Cake\Http\Response
	 */
	public function switchProject() {
		$projectId = !empty($this->request->data['project_switch']) ? (int)$this->request->data['project_switch'] : 0;
		if ($projectId && ($project = $this->TranslateProjects->get($projectId))) {
			$this->request->session()->write('TranslateProject.id', $project->translateProject['id']);
			$this->Flash->success(__('Project switched'));
		}

		return $this->Common->autoRedirect(['controller' => 'translate', 'action' => 'index']);
	}

	/**
	 * @return void
	 */
	public function reset() {
		$removeOptions = [
			'terms' => __('Translate Terms'),
			'strings' => __('Translate Strings'),
			'groups' => __('Translate Groups'),
		];
		$this->TranslateLanguage = TableRegistry::get('Translate.TranslateLanguage');
		$languages = $this->TranslateLanguages->find('list');
		$id = $this->request->session()->read('TranslateProject.id');

		if ($this->Common->isPosted()) {
			$this->TranslateProjects->reset($id, $this->request->data['Form']['reset'], $this->request->data['Form']['language']);

			$this->Flash->success(__('Done'));
			//$this->Common->autoRedirect(array('controller'=>'translate', 'action'=>'index'));

		} else {
			$this->request->data['Form']['reset'][] = 'terms';
			foreach ($languages as $key => $language) {
				$this->request->data['Form']['language'][] = $key;
			}

		}

		$this->set(compact('removeOptions', 'languages'));
	}

}
