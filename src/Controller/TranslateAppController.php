<?php

namespace Translate\Controller;

use App\Controller\AppController;
use Cake\Core\Configure;

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
	 * @return \Cake\Http\Response|null
	 */
	public function beforeFilter(EventInterface $event) {
		parent::beforeFilter($event);

		/*
		if ($this->request->getSession()->check('TranslateProject.id')) {
			return null;
		}

		$this->loadModel('Translate.TranslateProjects');
		$id = $this->TranslateProjects->getDefaultProjectId();
		$this->request->getSession()->write('TranslateProject.id', $id);
		*/

		return null;
	}

}
