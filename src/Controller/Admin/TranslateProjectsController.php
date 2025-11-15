<?php

namespace Translate\Controller\Admin;

use Translate\Controller\TranslateAppController;
use Translate\Model\Entity\TranslateProject;

/**
 * TranslateProjects Controller
 *
 * @property \Translate\Model\Table\TranslateProjectsTable $TranslateProjects
 * @property \Translate\Model\Table\TranslateLanguagesTable $TranslateLanguages
 * @method \Cake\Datasource\ResultSetInterface<\Translate\Model\Entity\TranslateProject> paginate(\Cake\Datasource\RepositoryInterface|\Cake\Datasource\QueryInterface|string|null $object = null, array $settings = [])
 * @property \Translate\Controller\Component\TranslationComponent $Translation
 */
class TranslateProjectsController extends TranslateAppController {

	/**
	 * Index method
	 *
	 * @return \Cake\Http\Response|null|void
	 */
	public function index() {
		$translateProjects = $this->paginate();

		if (!$translateProjects->count()) {
			return $this->redirect(['action' => 'add', '?' => ['name' => 'Default', 'default' => true, 'status' => TranslateProject::STATUS_HIDDEN]]);
		}

		$this->set(compact('translateProjects'));
	}

	/**
	 * View method
	 *
	 * @param string|null $id Translate Project id.
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 * @return \Cake\Http\Response|null|void
	 */
	public function view($id = null) {
		$translateProject = $this->TranslateProjects->get($id, [
			'contain' => ['TranslateDomains'],
		]);

		$this->set(compact('translateProject'));
	}

	/**
	 * Add method
	 *
	 * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
	 */
	public function add() {
		if ($this->TranslateProjects->find()->count() > 0) {
			$this->Flash->warning('Currently only one project is supported yet.');

			return $this->Translation->autoRedirect(['action' => 'index']);
		}

		$translateProject = $this->TranslateProjects->newEmptyEntity();
		if ($this->request->is('post')) {
			$translateProject = $this->TranslateProjects->patchEntity($translateProject, $this->request->getData());
			if ($this->TranslateProjects->save($translateProject)) {
				$this->Flash->success(__d('translate', 'The translate project has been saved.'));

				return $this->redirect(['action' => 'index']);
			}

			$this->Flash->error(__d('translate', 'The translate project could not be saved. Please, try again.'));
		} else {
			foreach ($this->request->getQuery() as $key => $value) {
				$this->request = $this->request->withData((string)$key, (string)$value);
			}
		}

		$this->set(compact('translateProject'));
		//$this->set('_serialize', ['translateProject']);
	}

	/**
	 * Edit method
	 *
	 * @param string|null $id Translate Project id.
	 * @throws \Cake\Http\Exception\NotFoundException When record not found.
	 * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
	 */
	public function edit($id = null) {
		$translateProject = $this->TranslateProjects->get($id, [
			'contain' => [],
		]);
		if ($this->request->is(['patch', 'post', 'put'])) {
			$translateProject = $this->TranslateProjects->patchEntity($translateProject, $this->request->getData());
			if ($this->TranslateProjects->save($translateProject)) {
				$this->Flash->success(__d('translate', 'The translate project has been saved.'));

				return $this->redirect(['action' => 'index']);
			}

			$this->Flash->error(__d('translate', 'The translate project could not be saved. Please, try again.'));
		}

		$this->set(compact('translateProject'));
		//$this->set('_serialize', ['translateProject']);
	}

	/**
	 * Delete method
	 *
	 * @param string|null $id Translate Project id.
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 * @return \Cake\Http\Response|null Redirects to index.
	 */
	public function delete($id = null) {
		$this->request->allowMethod(['post', 'delete']);
		$translateProject = $this->TranslateProjects->get($id);
		if ($this->TranslateProjects->delete($translateProject)) {
			$this->Flash->success(__d('translate', 'The translate project has been deleted.'));
		} else {
			$this->Flash->error(__d('translate', 'The translate project could not be deleted. Please, try again.'));
		}

		return $this->redirect(['action' => 'index']);
	}

	/**
	 * @return \Cake\Http\Response
	 */
	public function switchProject() {
		$projectId = (int)$this->request->getData('project_switch');
		$translateProject = $this->TranslateProjects->get($projectId);

		$this->request->getSession()->write('TranslateProject.id', $translateProject->id);
		$this->Flash->success(__d('translate', 'Project switched'));

		return $this->Translation->autoRedirect(['controller' => 'Translate', 'action' => 'index']);
	}

	/**
	 * @return void
	 */
	public function reset() {
		$removeOptions = [
			'terms' => __d('translate', 'Translate Terms'),
			'strings' => __d('translate', 'Translate Strings'),
			'groups' => __d('translate', 'Translate Domains'),
		];
		$translateLanguagesTable = $this->fetchTable('Translate.TranslateLanguages');
		$languages = $translateLanguagesTable->find('list');
		$id = $this->request->getSession()->read('TranslateProject.id');

		if ($this->Translation->isPosted()) {
			$this->TranslateProjects->reset($id, $this->request->getData('Form.reset'), $this->request->getData('Form.language'));

			$this->Flash->success(__d('translate', 'Done'));
			//$this->Translation->autoRedirect(array('controller'=>'translate', 'action'=>'index'));

		} else {
			$formArray = [];
			$formArray['Form']['reset'][] = 'terms';
			foreach ($languages as $key => $language) {
				$formArray['Form']['language'][] = $key;
			}
			$this->request = $this->request->withData('Form', $formArray);
		}

		$this->set(compact('removeOptions', 'languages'));
	}

}
