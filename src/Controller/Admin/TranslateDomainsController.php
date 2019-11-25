<?php
namespace Translate\Controller\Admin;

use Translate\Controller\TranslateAppController;

/**
 * TranslateDomains Controller
 *
 * @property \Translate\Model\Table\TranslateDomainsTable $TranslateDomains
 * @method \Translate\Model\Entity\TranslateDomain[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class TranslateDomainsController extends TranslateAppController {

	/**
	 * @var array
	 */
	public $paginate = ['order' => ['TranslateDomains.modified' => 'DESC']];

	/**
	 * Index method
	 *
	 * @return \Cake\Http\Response|null
	 */
	public function index() {
		$this->paginate = [
			'contain' => ['TranslateProjects'],
		];
		$translateDomains = $this->paginate();

		$this->set(compact('translateDomains'));
		$this->set('_serialize', ['translateDomains']);
	}

	/**
	 * View method
	 *
	 * @param string|null $id Translate Domain id.
	 * @return \Cake\Http\Response|null
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 */
	public function view($id = null) {
		$translateDomain = $this->TranslateDomains->get($id, [
			'contain' => ['TranslateProjects', 'TranslateStrings'],
		]);

		$this->set(compact('translateDomain'));
		$this->set('_serialize', ['translateDomain']);
	}

	/**
	 * Add method
	 *
	 * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
	 */
	public function add() {
		$translateDomain = $this->TranslateDomains->newEntity();
		if ($this->request->is('post')) {
			$this->request->data['translate_project_id'] = $this->Translation->currentProjectId();

			$translateDomain = $this->TranslateDomains->patchEntity($translateDomain, $this->request->getData());
			if ($this->TranslateDomains->save($translateDomain)) {
				$this->Flash->success(__d('translate', 'The translate domain has been saved.'));
				return $this->redirect(['action' => 'index']);
			}

			$this->Flash->error(__d('translate', 'The translate domain could not be saved. Please, try again.'));
		} else {
			$this->request->data['active'] = true;
		}

		$this->set(compact('translateDomain'));
	}

	/**
	 * Edit method
	 *
	 * @param string|null $id Translate Domain id.
	 * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
	 * @throws \Cake\Network\Exception\NotFoundException When record not found.
	 */
	public function edit($id = null) {
		$translateDomain = $this->TranslateDomains->get($id, [
			'contain' => ['TranslateStrings'],
		]);
		if ($this->request->is(['patch', 'post', 'put'])) {
			$translateDomain = $this->TranslateDomains->patchEntity($translateDomain, $this->request->getData());
			if ($this->TranslateDomains->save($translateDomain)) {
				$this->Flash->success(__d('translate', 'The translate domain has been saved.'));
				return $this->redirect(['action' => 'index']);
			}

			$this->Flash->error(__d('translate', 'The translate domain could not be saved. Please, try again.'));
		}
		$translateProjects = $this->TranslateDomains->TranslateProjects->find('list', ['limit' => 200]);
		$translateStrings = $this->TranslateDomains->TranslateStrings->find('list', ['limit' => 200]);

		$this->set(compact('translateDomain', 'translateProjects', 'translateStrings'));
		$this->set('_serialize', ['translateDomain']);
	}

	/**
	 * Delete method
	 *
	 * @param string|null $id Translate Domain id.
	 * @return \Cake\Http\Response|null Redirects to index.
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 */
	public function delete($id = null) {
		$this->request->allowMethod(['post', 'delete']);
		$translateDomain = $this->TranslateDomains->get($id);
		if ($this->TranslateDomains->delete($translateDomain)) {
			$this->Flash->success(__d('translate', 'The translate domain has been deleted.'));
		} else {
			$this->Flash->error(__d('translate', 'The translate domain could not be deleted. Please, try again.'));
		}
		return $this->redirect(['action' => 'index']);
	}

}
