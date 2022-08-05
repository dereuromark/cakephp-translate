<?php

namespace Translate\Controller\Admin;

use Translate\Controller\TranslateAppController;

/**
 * TranslateDomains Controller
 *
 * @property \Translate\Model\Table\TranslateDomainsTable $TranslateDomains
 * @method \Cake\Datasource\ResultSetInterface<\Translate\Model\Entity\TranslateDomain> paginate($object = null, array $settings = [])
 * @property \Translate\Controller\Component\TranslationComponent $Translation
 */
class TranslateDomainsController extends TranslateAppController {

	/**
	 * @var array
	 */
	public $paginate = ['order' => ['TranslateDomains.modified' => 'DESC']];

	/**
	 * Index method
	 *
	 * @return \Cake\Http\Response|null|void
	 */
	public function index() {
		$this->paginate = [
			'contain' => ['TranslateProjects'],
		];
		$translateDomains = $this->paginate()->toArray();

		$this->set(compact('translateDomains'));
		$this->set('_serialize', ['translateDomains']);
	}

	/**
	 * View method
	 *
	 * @param string|null $id Translate Domain id.
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 * @return \Cake\Http\Response|null|void
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
	 * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
	 */
	public function add() {
		$translateDomain = $this->TranslateDomains->newEmptyEntity();
		if ($this->request->is('post')) {
			$data = $this->request->getData();
			$data['translate_project_id'] = $this->Translation->currentProjectId();

			$translateDomain = $this->TranslateDomains->patchEntity($translateDomain, $data);
			if ($this->TranslateDomains->save($translateDomain)) {
				$this->Flash->success(__d('translate', 'The translate domain has been saved.'));

				return $this->redirect(['action' => 'index']);
			}

			$this->Flash->error(__d('translate', 'The translate domain could not be saved. Please, try again.'));
		} else {
			$this->request = $this->request->withData('active', true);
		}

		$this->set(compact('translateDomain'));
	}

	/**
	 * Edit method
	 *
	 * @param string|null $id Translate Domain id.
	 * @throws \Cake\Http\Exception\NotFoundException When record not found.
	 * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
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
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 * @return \Cake\Http\Response|null Redirects to index.
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
