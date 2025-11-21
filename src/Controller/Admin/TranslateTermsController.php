<?php

namespace Translate\Controller\Admin;

use Cake\Http\Exception\NotFoundException;
use Translate\Controller\TranslateAppController;

/**
 * TranslateTerms Controller
 *
 * @property \Translate\Model\Table\TranslateTermsTable $TranslateTerms
 * @property \Translate\Controller\Component\TranslationComponent $Translation
 * @property \Search\Controller\Component\SearchComponent $Search
 */
class TranslateTermsController extends TranslateAppController {

	/**
	 * @var array<string, mixed>
	 */
	protected array $paginate = ['order' => ['TranslateTerms.modified' => 'DESC']];

	/**
	 * @return void
	 */
	public function initialize(): void {
		parent::initialize();

		$this->loadComponent('Search.Search', [
			'actions' => ['index'],
		]);
	}

	/**
	 * Index method
	 *
	 * @return \Cake\Http\Response|null|void
	 */
	public function index() {
		$query = $this->TranslateTerms->find('search', search: $this->request->getQuery())
			->contain(['TranslateStrings' => 'TranslateDomains', 'TranslateLocales'])
			->innerJoinWith('TranslateStrings.TranslateDomains', function ($q) {
				return $q->where(['TranslateDomains.translate_project_id' => $this->Translation->currentProjectId()]);
			});
		$translateTerms = $this->paginate($query);

		$translateLocales = $this->TranslateTerms->TranslateLocales
			->find('list')
			->where(['translate_project_id' => $this->Translation->currentProjectId()])
			->orderBy(['TranslateLocales.name' => 'ASC']);

		$this->set(compact('translateTerms', 'translateLocales'));
		//$this->set('_serialize', ['translateTerms']);
	}

	/**
	 * View method
	 *
	 * @param string|null $id Translate Term id.
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 * @return \Cake\Http\Response|null|void
	 */
	public function view($id = null) {
		$translateTerm = $this->TranslateTerms->get($id, [
			'contain' => ['TranslateStrings' => 'TranslateDomains', 'TranslateLocales'],
		]);

		if ($translateTerm->translate_string->translate_domain->translate_project_id !== $this->Translation->currentProjectId()) {
			throw new NotFoundException(__d('translate', 'Term not found.'));
		}

		$this->set(compact('translateTerm'));
		//$this->set('_serialize', ['translateTerm']);
	}

	/**
	 * Edit method
	 *
	 * @param string|null $id Translate Term id.
	 * @throws \Cake\Http\Exception\NotFoundException When record not found.
	 * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
	 */
	public function edit($id = null) {
		$translateTerm = $this->TranslateTerms->get($id, [
			'contain' => ['TranslateStrings' => 'TranslateDomains', 'TranslateLocales'],
		]);

		if ($translateTerm->translate_string->translate_domain->translate_project_id !== $this->Translation->currentProjectId()) {
			throw new NotFoundException(__d('translate', 'Term not found.'));
		}

		if ($this->request->is(['patch', 'post', 'put'])) {
			$translateTerm = $this->TranslateTerms->patchEntity($translateTerm, $this->request->getData());
			if ($this->TranslateTerms->save($translateTerm)) {
				$this->Flash->success(__d('translate', 'The translate term has been saved.'));

				return $this->redirect(['action' => 'index']);
			}

			$this->Flash->error(__d('translate', 'The translate term could not be saved. Please, try again.'));
		}
		$translateStrings = $this->TranslateTerms->TranslateStrings
			->find('list')
			->innerJoinWith('TranslateDomains', function ($q) {
				return $q->where(['TranslateDomains.translate_project_id' => $this->Translation->currentProjectId()]);
			});
		$translateLocales = $this->TranslateTerms->TranslateLocales
			->find('list')
			->where(['translate_project_id' => $this->Translation->currentProjectId()]);

		$this->set(compact('translateTerm', 'translateStrings', 'translateLocales'));
		//$this->set('_serialize', ['translateTerm']);
	}

	/**
	 * Delete method
	 *
	 * @param string|null $id Translate Term id.
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 * @return \Cake\Http\Response|null Redirects to index.
	 */
	public function delete($id = null) {
		$this->request->allowMethod(['post', 'delete']);
		$translateTerm = $this->TranslateTerms->get($id, [
			'contain' => ['TranslateStrings' => 'TranslateDomains'],
		]);

		if ($translateTerm->translate_string->translate_domain->translate_project_id !== $this->Translation->currentProjectId()) {
			throw new NotFoundException(__d('translate', 'Term not found.'));
		}

		if ($this->TranslateTerms->delete($translateTerm)) {
			$this->Flash->success(__d('translate', 'The translate term has been deleted.'));
		} else {
			$this->Flash->error(__d('translate', 'The translate term could not be deleted. Please, try again.'));
		}

		return $this->redirect(['action' => 'index']);
	}

	/**
	 * Pending translations dashboard
	 *
	 * @return \Cake\Http\Response|null|void
	 */
	public function pending() {
		$projectId = $this->Translation->currentProjectId();

		$query = $this->TranslateTerms->find()
			->where([
				'TranslateTerms.confirmed' => false,
				'TranslateTerms.content IS NOT' => null,
				'TranslateTerms.content !=' => '',
			])
			->contain(['TranslateStrings' => 'TranslateDomains', 'TranslateLocales'])
			->innerJoinWith('TranslateStrings.TranslateDomains', function ($q) use ($projectId) {
				return $q->where([
					'TranslateDomains.translate_project_id' => $projectId,
					'TranslateDomains.active' => true,
				]);
			})
			->orderBy(['TranslateTerms.modified' => 'DESC']);

		$pendingTerms = $this->paginate($query);

		// Get counts per locale
		$localeStats = [];
		$locales = $this->TranslateTerms->TranslateLocales
			->find()
			->where(['translate_project_id' => $projectId, 'active' => true])
			->toArray();

		foreach ($locales as $locale) {
			$count = $this->TranslateTerms->find()
				->where([
					'TranslateTerms.translate_locale_id' => $locale->id,
					'TranslateTerms.confirmed' => false,
					'TranslateTerms.content IS NOT' => null,
					'TranslateTerms.content !=' => '',
				])
				->innerJoinWith('TranslateStrings.TranslateDomains', function ($q) use ($projectId) {
					return $q->where([
						'TranslateDomains.translate_project_id' => $projectId,
						'TranslateDomains.active' => true,
					]);
				})
				->count();

			$localeStats[$locale->id] = [
				'name' => $locale->name,
				'locale' => $locale->locale,
				'count' => $count,
			];
		}

		$this->set(compact('pendingTerms', 'localeStats'));
	}

	/**
	 * Batch confirm translations
	 *
	 * @return \Cake\Http\Response|null Redirects to pending.
	 */
	public function batchConfirm() {
		$this->request->allowMethod(['post']);
		$projectId = $this->Translation->currentProjectId();

		$localeId = $this->request->getData('locale_id');
		$confirmAll = $this->request->getData('confirm_all');

		$conditions = [
			'TranslateTerms.confirmed' => false,
			'TranslateTerms.content IS NOT' => null,
			'TranslateTerms.content !=' => '',
		];

		if ($localeId && !$confirmAll) {
			$conditions['TranslateTerms.translate_locale_id'] = $localeId;
		}

		$terms = $this->TranslateTerms->find()
			->where($conditions)
			->innerJoinWith('TranslateStrings.TranslateDomains', function ($q) use ($projectId) {
				return $q->where([
					'TranslateDomains.translate_project_id' => $projectId,
					'TranslateDomains.active' => true,
				]);
			})
			->toArray();

		$count = 0;
		foreach ($terms as $term) {
			$term->confirmed = 1;
			if ($this->TranslateTerms->save($term)) {
				$count++;
			}
		}

		if ($count > 0) {
			$this->Flash->success(__d('translate', '{0} translation(s) have been confirmed.', $count));
		} else {
			$this->Flash->warning(__d('translate', 'No translations were confirmed.'));
		}

		return $this->redirect(['action' => 'pending']);
	}

	/**
	 * Confirm a single translation term
	 *
	 * @param int $id Term ID
	 * @return \Cake\Http\Response|null Redirects to pending.
	 */
	public function confirm(int $id) {
		$this->request->allowMethod(['post', 'delete']);

		$term = $this->TranslateTerms->get($id, [
			'contain' => ['TranslateStrings' => 'TranslateDomains'],
		]);

		if ($term->translate_string->translate_domain->translate_project_id !== $this->Translation->currentProjectId()) {
			$this->Flash->error(__d('translate', 'Term not found.'));

			return $this->redirect(['action' => 'pending']);
		}

		$term->confirmed = true;
		if ($this->TranslateTerms->save($term)) {
			$this->Flash->success(__d('translate', 'Translation has been confirmed.'));
		} else {
			$this->Flash->error(__d('translate', 'Could not confirm translation.'));
		}

		return $this->redirect(['action' => 'pending']);
	}

}
