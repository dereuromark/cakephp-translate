<?php

namespace Translate\Controller\Admin;

use Translate\Controller\TranslateAppController;
use Translate\Lib\ConvertLib;
use Translate\Translator\Translator;

/**
 * @property \Translate\Model\Table\TranslateDomainsTable $TranslateDomains
 * @property \Translate\Model\Table\TranslateLanguagesTable $TranslateLanguages
 * @property \Translate\Controller\Component\TranslationComponent $Translation
 */
class TranslateController extends TranslateAppController {

	/**
	 * @var string
	 */
	public $modelClass = 'Translate.TranslateDomains';

	/**
	 * Initial page / overview
	 *
	 * @return void
	 */
	public function index() {
		$this->loadModel('Translate.TranslateLanguages');
		$languages = $this->TranslateLanguages->find('all')->toArray();

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
		if ($this->Common->isPosted()) {
			$selection = (array)$this->request->getData('Form.sel');
			foreach ($selection as $sel) {
				if (!empty($sel)) {
					switch ($sel) {
						case 'terms':
							$this->TranslateDomains->TranslateStrings->TranslateTerms->truncate();

							break;
						case 'strings':
							$this->TranslateDomains->TranslateStrings->truncate();

							break;
						case 'groups':
							$this->TranslateDomains->truncate();

							break;
						case 'languages':
							$this->TranslateDomains->TranslateStrings->TranslateTerms->TranslateLanguages->truncate();

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
		$this->set('_serialize', true);
	}

	/**
	 * Convert text for PO files.
	 *
	 * @return void
	 */
	public function convert() {
		if ($this->Common->isPosted()) {
			$settings = $this->request->getData('Translate');
			$text = $this->request->getData('input');

			$ConvertLib = new ConvertLib();
			$text = $ConvertLib->convert($text, $settings);
			$this->set(compact('text'));
		}
	}

}
