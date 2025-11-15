<?php

namespace Translate\Controller\Admin;

use Translate\Controller\TranslateAppController;
use Translate\Filesystem\Creator;

/**
 * TranslateLocales Controller
 *
 * @property \Translate\Model\Table\TranslateLocalesTable $TranslateLocales
 * @method \Cake\Datasource\ResultSetInterface<\Translate\Model\Entity\TranslateLocale> paginate(\Cake\Datasource\RepositoryInterface|\Cake\Datasource\QueryInterface|string|null $object = null, array $settings = [])
 * @property \Translate\Controller\Component\TranslationComponent $Translation
 */
class TranslateLocalesController extends TranslateAppController {

	/**
	 * @var array<string, mixed>
	 */
	protected array $paginate = ['order' => ['TranslateLocales.name' => 'ASC']];

	/**
	 * @return \Cake\Http\Response|null|void
	 */
	public function toLocale() {
		$path = LOCALE;

		$creator = new Creator();
		$existingFolders = $creator->findLocaleFolders($path);
		$languages = $this->TranslateLocales->find('list', ['keyField' => 'locale'])->toArray();

		if ($this->Translation->isPosted()) {
			$data = [];
			$locales = (array)$this->request->getData('locale');
			foreach ($locales as $lang => $value) {
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
			$localeArray = [];
			foreach ($languages as $k => $v) {
				$localeArray[$k] = true;
			}
			$this->request = $this->request->withData('locale', $localeArray);
		}

		$this->set(compact('path', 'languages', 'existingFolders'));
	}

	/**
	 * @return \Cake\Http\Response|null|void
	 */
	public function fromLocale() {
		$path = LOCALE;

		$creator = new Creator();
		$folders = $creator->findLocaleFolders($path);
		$existingLanguages = $this->TranslateLocales->find('list', ['keyField' => 'locale'])->toArray();

		if ($this->Translation->isPosted()) {
			$translateLocales = [];
			$languages = (array)$this->request->getData('language');
			foreach ($languages as $key => $data) {
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

				$translateLocales[] = $this->TranslateLocales->newEntity($data);
			}

			if (!empty($data) && $this->TranslateLocales->saveMany($translateLocales)) {
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
	 * @return \Cake\Http\Response|null|void
	 */
	public function index() {
		$translateLocales = $this->paginate();

		$this->set(compact('translateLocales'));
		//$this->set('_serialize', ['translateLocales']);
	}

	/**
	 * View method
	 *
	 * @param string|null $id Translate Language id.
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 * @return \Cake\Http\Response|null|void
	 */
	public function view($id = null) {
		$translateLocale = $this->TranslateLocales->get($id, [
			'contain' => ['TranslateTerms'],
		]);

		$this->set(compact('translateLocale'));
		//$this->set('_serialize', ['translateLocale']);
	}

	/**
	 * Add method
	 *
	 * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
	 */
	public function add() {
		$translateLocale = $this->TranslateLocales->newEmptyEntity();
		if ($this->request->is('post')) {
			$data = $this->request->getData();
			$data['translate_project_id'] = $this->Translation->currentProjectId();

			$translateLocale = $this->TranslateLocales->patchEntity($translateLocale, $data);
			if ($this->TranslateLocales->save($translateLocale)) {
				$this->Flash->success(__d('translate', 'The translate language has been saved.'));

				return $this->redirect(['action' => 'index']);
			}

			$this->Flash->error(__d('translate', 'The translate language could not be saved. Please, try again.'));
		}

		$this->set(compact('translateLocale'));
		//$this->set('_serialize', ['translateLocale']);
	}

	/**
	 * Edit method
	 *
	 * @param string|null $id Translate Language id.
	 * @throws \Cake\Http\Exception\NotFoundException When record not found.
	 * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
	 */
	public function edit($id = null) {
		$translateLocale = $this->TranslateLocales->get($id, [
			'contain' => [],
		]);
		if ($this->request->is(['patch', 'post', 'put'])) {
			$translateLocale = $this->TranslateLocales->patchEntity($translateLocale, $this->request->getData());
			if ($this->TranslateLocales->save($translateLocale)) {
				$this->Flash->success(__d('translate', 'The translate language has been saved.'));

				return $this->redirect(['action' => 'index']);
			}

			$this->Flash->error(__d('translate', 'The translate language could not be saved. Please, try again.'));
		}

		$this->set(compact('translateLocale'));
		//$this->set('_serialize', ['translateLocale']);
	}

	/**
	 * Delete method
	 *
	 * @param string|null $id Translate Language id.
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 * @return \Cake\Http\Response|null Redirects to index.
	 */
	public function delete($id = null) {
		$this->request->allowMethod(['post', 'delete']);
		$translateLocale = $this->TranslateLocales->get($id);
		if ($this->TranslateLocales->delete($translateLocale)) {
			$this->Flash->success(__d('translate', 'The translate language has been deleted.'));
		} else {
			$this->Flash->error(__d('translate', 'The translate language could not be deleted. Please, try again.'));
		}

		return $this->redirect(['action' => 'index']);
	}

}
