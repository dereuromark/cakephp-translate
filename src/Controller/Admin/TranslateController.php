<?php
namespace Translate\Controller\Admin;

use Translate\Controller\TranslateAppController;
use Translate\Lib\ConvertLib;
use Translate\Translator\Translator;

/**
 * @property \Translate\Model\Table\TranslateGroupsTable $TranslateGroups
 * @property \Translate\Model\Table\TranslateLanguagesTable $TranslateLanguages
 */
class TranslateController extends TranslateAppController {

	/**
	 * @var array
	 */
	public $helpers = [
		'Translate.Translation',
	];

	/**
	 * @var string
	 */
	public $modelClass = 'Translate.TranslateGroups';

	/**
	 * Initial page / overview
	 *
	 * @return void
	 */
	public function index() {
		$this->loadModel('Translate.TranslateLanguages');
		$languages = $this->TranslateLanguages->find('all', ['contain' => []]);

		$id = $this->request->session()->read('TranslateProject.id');
		$count = $this->TranslateGroups->statistics($id, $languages->toArray());
		$coverage = $this->TranslateGroups->TranslateStrings->coverage($this->Translation->currentProjectId());
		$projectSwitchArray = $this->TranslateGroups->TranslateProjects->find('list');
		$this->set(compact('coverage', 'languages', 'count', 'projectSwitchArray'));
	}

	/**
	 * @return void
	 */
	public function bestPractice() {
	}

	/**
	 * Reset terms and strings
	 * - optional: groups and languages
	 *
	 * @return \Cake\Http\Response|null
	 */
	public function reset() {
		if ($this->Common->isPosted()) {
			foreach ($this->request->data['Form']['sel'] as $sel) {
				if (!empty($sel)) {
					switch ($sel) {
						case 'terms':
							$this->TranslateGroups->TranslateStrings->TranslateTerms->truncate();
							break;
						case 'strings':
							$this->TranslateGroups->TranslateStrings->truncate();
							break;
						case 'groups':
							$this->TranslateGroups->truncate();
							break;
						case 'languages':
							$this->TranslateGroups->TranslateLanguages->truncate();
							break;
					}
				}
			}
			return $this->Common->postRedirect(['action' => 'reset']);
		}

		$this->request->data['Form']['sel'][] = 'terms';
		$this->request->data['Form']['sel'][] = 'strings';
		//$this->request->data['Form']['sel']['languages'] = 0;
		//$this->request->data['Form']['sel']['groups'] = 0;
	}

	/**
	 * @return void
	 */
	public function translate() {
		$this->request->allowMethod(['post']);

		$text = $this->request->data('text');
		$to = $this->request->data('to');
		$from = $this->request->data('from');

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
			$settings = $this->request->data('Translate');
			$text = $this->request->data['input'];

			$this->ConvertLib = new ConvertLib();
			$text = $this->ConvertLib->convert($text, $settings);
			$this->set(compact('text'));
		}
	}

}
