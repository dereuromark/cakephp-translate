<?php

namespace Translate\Test\TestCase;

use Cake\Core\Configure;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * Base Integration Test Case for controller tests
 */
abstract class IntegrationTestCase extends TestCase {

	use IntegrationTestTrait;

	/**
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		// The plugin's beforeFilter requires `Translate.adminAccess` to be a Closure
		// returning true for permitted callers. Tests run as a permitted caller by
		// default; individual tests can override the gate to assert the deny path.
		Configure::write('Translate.adminAccess', function ($request): bool {
			return true;
		});
	}

	/**
	 * @return void
	 */
	protected function tearDown(): void {
		Configure::delete('Translate.adminAccess');

		parent::tearDown();
	}

}
