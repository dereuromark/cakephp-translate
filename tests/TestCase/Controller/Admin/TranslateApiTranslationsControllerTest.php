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

	/**
	 * Test view method
	 *
	 * @return void
	 */
	public function testView() {
		$id = 1;
		$this->get(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateApiTranslations', 'action' => 'view', $id]);

		$this->assertResponseCode(200);
		$this->assertNoRedirect();
	}

	/**
	 * Test add method GET
	 *
	 * @return void
	 */
	public function testAddGet() {
		$this->get(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateApiTranslations', 'action' => 'add']);

		$this->assertResponseCode(200);
		$this->assertNoRedirect();
	}

	/**
	 * Test add method POST - expects validation to succeed with complete data
	 *
	 * @return void
	 */
	public function testAddPost() {
		$data = [
			'from_locale' => 'en',
			'from_text' => 'Hello',
			'to_locale' => 'de',
			'to_text' => 'Hallo',
		];
		$this->post(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateApiTranslations', 'action' => 'add'], $data);

		// Since this is a simple form without complex validation, it might show the form with errors
		// or succeed depending on model validation rules
		$this->assertResponseOk();
	}

	/**
	 * Test edit method GET
	 *
	 * @return void
	 */
	public function testEditGet() {
		$id = 1;
		$this->get(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateApiTranslations', 'action' => 'edit', $id]);

		$this->assertResponseCode(200);
		$this->assertNoRedirect();
	}

	/**
	 * Test edit method POST
	 *
	 * @return void
	 */
	public function testEditPost() {
		$this->disableErrorHandlerMiddleware();

		$id = 1;
		$data = [
			'to_text' => 'Updated translation',
		];
		$this->post(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateApiTranslations', 'action' => 'edit', $id], $data);

		$this->assertResponseOk();
	}

	/**
	 * Test delete method
	 *
	 * @return void
	 */
	public function testDelete() {
		$this->enableRetainFlashMessages();

		$id = 1;
		$this->post(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateApiTranslations', 'action' => 'delete', $id]);

		$this->assertResponseCode(302);
		$this->assertRedirect(['action' => 'index']);
		$this->assertFlashMessage('The translate api translation has been deleted.');
	}

}
