<?php

namespace Translate\Controller;

use App\Controller\AppController;
use BootstrapUI\View\Helper\FormHelper;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\I18n\I18n;

/**
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

		// Set translation manager locale after parent initialize
		$locale = $this->request->getSession()->read('Config.language');
		if ($locale && $locale !== Configure::read('Config.language')) {
			I18n::setLocale($locale);
		}

		$this->loadComponent('Translate.Translation');

		$this->viewBuilder()->addHelper('Translate.Translation');
		$this->viewBuilder()->addHelper('Translate.Icon');

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

		// Apply language from session, or default to English for translation manager
		$locale = $this->request->getSession()->read('Config.language');
		if (!$locale) {
			$locale = 'en_US'; // Default translation manager interface to English
		}
		I18n::setLocale($locale);
		$this->request = $this->request->withAttribute('locale', $locale);

		// Auto-select default project if none selected (except for homepage and projects controller)
		$controller = $this->request->getParam('controller');
		$action = $this->request->getParam('action');
		$isHomepage = ($controller === 'Translate' && $action === 'index');
		$isProjectsController = ($controller === 'TranslateProjects');

		if (!$isHomepage && !$isProjectsController && !$this->request->getSession()->check('TranslateProject.id')) {
			$translateProjects = $this->fetchTable('Translate.TranslateProjects');
			$id = $translateProjects->getDefaultProjectId();
			if ($id) {
				$this->request->getSession()->write('TranslateProject.id', $id);
			}
		}
	}

	/**
	 * @param \Cake\Event\EventInterface $event
	 * @return void
	 */
	public function beforeRender(EventInterface $event): void {
		// Only set default layout if one hasn't been explicitly set
		if ($this->viewBuilder()->getLayout() === null) {
			$layout = Configure::read('Translate.layout', 'Translate.simple');
			$this->viewBuilder()->setLayout($layout);
		}

		$map = Configure::read('Translate.iconMap');
		if ($map) {
			$map += (array)Configure::read('Icon.map');
			Configure::write('Icon.map', $map);
		}

		if (class_exists(FormHelper::class)) {
			$this->viewBuilder()->addHelper('BootstrapUi.Form');
		}

		// Make current project available to all views
		$projectId = $this->request->getSession()->read('TranslateProject.id');
		if ($projectId) {
			$translateProjects = $this->fetchTable('Translate.TranslateProjects');
			$currentProject = $translateProjects->find()
				->where(['id' => $projectId])
				->first();
			$this->set('currentProject', $currentProject);
		} else {
			$this->set('currentProject', null);
		}
	}

}
