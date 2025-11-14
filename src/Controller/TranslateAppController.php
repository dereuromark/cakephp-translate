<?php

namespace Translate\Controller;

use App\Controller\AppController;
use BootstrapUI\View\Helper\FormHelper;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Templating\TemplatingPlugin;

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
		if (class_exists(TemplatingPlugin::class)) {
			$this->viewBuilder()->addHelper('Templating.Icon');
			$this->viewBuilder()->addHelper('Templating.IconSnippet');
		}

		if (!$this->components()->has('Flash')) {
			$this->loadComponent('Flash');
		}
	}

	/**
	 * @param \Cake\Event\EventInterface $event
	 *
	 * @return void
	 */
	public function beforeFilter(EventInterface $event): void {
		parent::beforeFilter($event);

		/*
		if ($this->request->getSession()->check('TranslateProject.id')) {
			return;
		}

		$this->loadModel('Translate.TranslateProjects');
		$id = $this->TranslateProjects->getDefaultProjectId();
		$this->request->getSession()->write('TranslateProject.id', $id);
		*/
	}

	/**
	 * @param \Cake\Event\EventInterface $event
	 * @return void
	 */
	public function beforeRender(EventInterface $event): void {
		$layout = Configure::read('Translate.layout', 'Translate.simple');
		$this->viewBuilder()->setLayout($layout);

		$map = Configure::read('Translate.iconMap');
		if ($map) {
			$map += (array)Configure::read('Icon.map');
			Configure::write('Icon.map', $map);
		}

		if (class_exists(FormHelper::class)) {
			$this->viewBuilder()->addHelper('BootstrapUi.Form');
		}
	}

}
