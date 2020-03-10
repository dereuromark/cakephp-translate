<?php

namespace App\Controller;

use Shim\Controller\Controller;

class AppController extends Controller {

	/**
	 * @return void
	 */
	public function initialize(): void {
		parent::initialize();

		$this->loadComponent('Flash');
		$this->loadComponent('RequestHandler');

		$this->viewBuilder()->setHelpers(['Tools.Format']);
	}

}
