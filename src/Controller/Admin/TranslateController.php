<?php

namespace Translate\Controller\Admin;

use Cake\View\JsonView;
use Translate\Controller\TranslateAppController;
use Translate\Lib\ConvertLib;
use Translate\Translator\Translator;

/**
 * @property \Translate\Model\Table\TranslateDomainsTable $TranslateDomains
 * @property \Translate\Model\Table\TranslateLocalesTable $TranslateLocales
 * @property \Translate\Controller\Component\TranslationComponent $Translation
 */
class TranslateController extends TranslateAppController {

	/**
	 * @var string|null
	 */
	protected ?string $defaultTable = 'Translate.TranslateDomains';

	/**
	 * Get alternate view classes that can be used in
	 * content-type negotiation.
	 *
	 * @return list<string>
	 */
	public function viewClasses(): array {
		return [JsonView::class];
	}

	/**
	 * Initial page / overview
	 *
	 * @return void
	 */
	public function index() {
		$translateLocalesTable = $this->fetchTable('Translate.TranslateLocales');
		$languages = $translateLocalesTable->find('all')->toArray();

		$id = $this->request->getSession()->read('TranslateProject.id');
		$count = $id ? $this->TranslateDomains->statistics($id, $languages) : 0;
		$coverage = $this->TranslateDomains->TranslateStrings->coverage($this->Translation->currentProjectId());
		$projectSwitchArray = $this->TranslateDomains->TranslateProjects->find('list')->toArray();
		$this->set(compact('coverage', 'languages', 'count', 'projectSwitchArray'));
	}

	/**
	 * @return void
	 */
	public function bestPractice() {
	}

	/**
	 * Reset terms and strings
	 * - optional: domains and languages
	 *
	 * @return \Cake\Http\Response|null|void
	 */
	public function reset() {
		if ($this->Translation->isPosted()) {
			$selection = (array)$this->request->getData('Form.sel');
			if ($this->request->getQuery('hard-reset')) {
				$this->Translation->hardReset();
				$this->Flash->success('Hard reset done.');

				return $this->redirect(['action' => 'index']);
			}

			foreach ($selection as $sel) {
				if (!empty($sel)) {
					switch ($sel) {
						case 'terms':
							$this->TranslateDomains->TranslateStrings->TranslateTerms->deleteAll('1=1');

							break;
						case 'strings':
							$this->TranslateDomains->TranslateStrings->deleteAll('1=1');

							break;
						case 'groups':
							$this->TranslateDomains->deleteAll('1=1');

							break;
						case 'languages':
							$this->TranslateDomains->TranslateStrings->TranslateTerms->TranslateLocales->deleteAll('1=1');

							break;
					}
				}

			}
			$this->Flash->success('Done');

			return $this->redirect(['action' => 'index']);
		}

		//FIXME
		//$this->request->data['Form']['sel'][] = 'terms';
		//$this->request->data['Form']['sel'][] = 'strings';

		//$this->request->data['Form']['sel']['languages'] = 0;
		//$this->request->data['Form']['sel']['groups'] = 0;
	}

	/**
	 * @return void
	 */
	public function translate() {
		$this->request->allowMethod(['post']);

		$text = $this->request->getData('text');
		$to = $this->request->getData('to');
		$from = $this->request->getData('from');

		$translator = new Translator();
		$translation = $translator->translate($text, $to, $from);

		$this->set(compact('translation'));
		$serialize = true;
		$this->viewBuilder()->setOptions(compact('serialize'));
	}

	/**
	 * Convert text for PO files.
	 *
	 * @return void
	 */
	public function convert() {
		if ($this->Translation->isPosted()) {
			$settings = (array)$this->request->getData();
			$text = $this->request->getData('input');

			$ConvertLib = new ConvertLib();
			$text = $ConvertLib->convert($text, $settings);
			$this->set(compact('text'));
		}
	}

	/**
	 * Switch the application language/locale
	 *
	 * @return \Cake\Http\Response
	 */
	public function switchLanguage() {
		$locale = $this->request->getQuery('locale');
		if (!$locale) {
			$this->Flash->error(__d('translate', 'Invalid locale'));

			return $this->redirect(['action' => 'index']);
		}

		// Validate locale exists and is active
		$language = $this->fetchTable('Translate.TranslateLocales')
			->find()
			->where([
				'locale' => $locale,
				'active' => true,
			])
			->first();

		if (!$language) {
			$this->Flash->error(__d('translate', 'Language not found or not active'));

			return $this->redirect(['action' => 'index']);
		}

		// Store locale in session or cookie
		$this->request->getSession()->write('Config.language', $locale);

		$this->Flash->success(__d('translate', 'Language switched to {0}', $language->name));

		// Redirect back to previous page or index
		$redirect = $this->request->getQuery('redirect') ?: ['action' => 'index'];

		return $this->redirect($redirect);
	}

}
