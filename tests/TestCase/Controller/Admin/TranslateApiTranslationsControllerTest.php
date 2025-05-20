<?php

namespace Translate\Test\TestCase\Controller\Admin;

use Shim\TestSuite\IntegrationTestCase;

/**
 * Translate\Controller\Admin\TranslateApiTranslationsController Test Case
 *
 * @uses \Translate\Controller\Admin\TranslateApiTranslationsController
 */
class TranslateApiTranslationsControllerTest extends IntegrationTestCase {

	/**
	 * Fixtures
	 *
	 * @var array<string>
	 */
	protected array $fixtures = [
		'plugin.Translate.TranslateApiTranslations',
	];

	/**
	 * Test index method
	 *
	 * @return void
	 */
	public function testIndex() {
		$this->get(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateApiTranslations', 'action' => 'index']);

		$this->assertResponseCode(200);
		$this->assertNoRedirect();
	}

}
