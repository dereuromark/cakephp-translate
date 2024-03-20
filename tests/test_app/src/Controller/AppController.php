<?php

namespace TestApp\Controller;

use Shim\Controller\Controller;
use Templating\TemplatingPlugin;

class AppController extends Controller {

	/**
	 * @return void
	 */
	public function initialize(): void {
		parent::initialize();

		$this->loadComponent('Flash');

		$this->viewBuilder()->setHelpers(['Tools.Format']);
		if (class_exists(TemplatingPlugin::class)) {
			$this->viewBuilder()->addHelper('Templating.Icon');
			$this->viewBuilder()->addHelper('Templating.IconSnippet');
		}
	}

}
