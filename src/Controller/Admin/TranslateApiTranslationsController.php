<?php

namespace Translate\Controller\Admin;

use App\Controller\AppController;

/**
 * TranslateApiTranslations Controller
 *
 * @property \Translate\Model\Table\TranslateApiTranslationsTable $TranslateApiTranslations
 * @method \Cake\Datasource\ResultSetInterface<\Translate\Model\Entity\TranslateApiTranslation> paginate(\Cake\Datasource\RepositoryInterface|\Cake\Datasource\QueryInterface|string|null $object = null, array $settings = [])
 */
class TranslateApiTranslationsController extends AppController {

	/**
	 * Index method
	 *
	 * @return \Cake\Http\Response|null|void
	 */
	public function index() {
		$translateApiTranslations = $this->paginate();

		$this->set(compact('translateApiTranslations'));
		//$this->set('_serialize', ['translateApiTranslations']);
	}

	/**
	 * View method
	 *
	 * @param string|null $id Translate Api Translation id.
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 * @return \Cake\Http\Response|null|void
	 */
	public function view($id = null) {
		$translateApiTranslation = $this->TranslateApiTranslations->get($id, [
			'contain' => [],
		]);

		$this->set(compact('translateApiTranslation'));
		//$this->set('_serialize', ['translateApiTranslation']);
	}

	/**
	 * Add method
	 *
	 * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
	 */
	public function add() {
		$translateApiTranslation = $this->TranslateApiTranslations->newEmptyEntity();
		if ($this->request->is('post')) {
			$translateApiTranslation = $this->TranslateApiTranslations->patchEntity($translateApiTranslation, $this->request->getData());
			if ($this->TranslateApiTranslations->save($translateApiTranslation)) {
				$this->Flash->success(__d('translate', 'The translate api translation has been saved.'));

				return $this->redirect(['action' => 'index']);
			}

			$this->Flash->error(__d('translate', 'The translate api translation could not be saved. Please, try again.'));
		}

		$this->set(compact('translateApiTranslation'));
		//$this->set('_serialize', ['translateApiTranslation']);
	}

	/**
	 * Edit method
	 *
	 * @param string|null $id Translate Api Translation id.
	 * @throws \Cake\Http\Exception\NotFoundException When record not found.
	 * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
	 */
	public function edit($id = null) {
		$translateApiTranslation = $this->TranslateApiTranslations->get($id, [
			'contain' => [],
		]);
		if ($this->request->is(['patch', 'post', 'put'])) {
			$translateApiTranslation = $this->TranslateApiTranslations->patchEntity($translateApiTranslation, $this->request->getData());
			if ($this->TranslateApiTranslations->save($translateApiTranslation)) {
				$this->Flash->success(__d('translate', 'The translate api translation has been saved.'));

				return $this->redirect(['action' => 'index']);
			}

			$this->Flash->error(__d('translate', 'The translate api translation could not be saved. Please, try again.'));
		}

		$this->set(compact('translateApiTranslation'));
		//$this->set('_serialize', ['translateApiTranslation']);
	}

	/**
	 * Delete method
	 *
	 * @param string|null $id Translate Api Translation id.
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 * @return \Cake\Http\Response|null Redirects to index.
	 */
	public function delete($id = null) {
		$this->request->allowMethod(['post', 'delete']);
		$translateApiTranslation = $this->TranslateApiTranslations->get($id);
		if ($this->TranslateApiTranslations->delete($translateApiTranslation)) {
			$this->Flash->success(__d('translate', 'The translate api translation has been deleted.'));
		} else {
			$this->Flash->error(__d('translate', 'The translate api translation could not be deleted. Please, try again.'));
		}

		return $this->redirect(['action' => 'index']);
	}

}
