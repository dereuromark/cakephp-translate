<?php
namespace Translate\Controller;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Event\Event;

# fix for internal routing (sticky plugin name in url)
Configure::write('Plugin.name', 'Translate');

/**
 * @property \Tools\Controller\Component\CommonComponent $Common
 * @property \Shim\Controller\Component\SessionComponent $Session
 * @property \Translate\Model\Table\TranslateProjectsTable $TranslateProjects
 * @property \Translate\Controller\Component\TranslationComponent $Translation
 */
class TranslateAppController extends AppController {

	/**
	 * @var array
	 */
	public $helpers = ['Translate.Translation', 'Tools.Format'];

	/**
	 * @var array
	 */
	public $components = ['Translate.Translation', 'Tools.Common'];

	/**
	 * @param \Cake\Event\Event $event
	 *
	 * @return void
	 */
	public function beforeFilter(Event $event) {
		parent::beforeFilter($event);

		/*
		if ($this->request->session()->check('TranslateProject.id')) {
			return;
		}

		$this->loadModel('Translate.TranslateProjects');
		$id = $this->TranslateProjects->getDefaultProjectId();
		$this->request->session()->write('TranslateProject.id', $id);
		*/
	}

}
