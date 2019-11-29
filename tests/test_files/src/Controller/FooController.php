<?php

namespace App\Controller;

use Cake\Controller\Controller;

class FooController extends Controller {

	/**
	 * @return void
	 */
	public function index() {
		__('xyz');
		__d('foo', 'foobar');
	}

}
