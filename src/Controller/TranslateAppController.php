<?php

namespace Translate\Controller;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Event\EventInterface;

# fix for internal routing (sticky plugin name in url)
Configure::write('Plugin.name', 'Translate');

/**
 * @property \Tools\Controller\Component\CommonComponent $Common
 * @property \Translate\Model\Table\TranslateProjectsTable $TranslateProjects
 * @property \Translate\Controller\Component\TranslationComponent $Translation
 */
class TranslateAppController extends AppController {

	/**
	 * @throws \Exception
	 * @return void
	 */
	public function initialize(): void {
		parent::initialize();

		$this->loadComponent('Translate.Translation');
		$this->loadComponent('Tools.Common');

		$this->viewBuilder()->addHelper('Translate.Translation');
		$this->viewBuilder()->addHelper('Tools.Format');
	}

	/**
	 * @param \Cake\Event\EventInterface $event
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
