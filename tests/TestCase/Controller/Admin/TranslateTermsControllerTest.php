<?php

namespace Translate\Test\TestCase\Controller\Admin;

use Translate\Test\TestCase\IntegrationTestCase;

/**
 * Translate\Controller\Admin\TranslateTermsController Test Case
 *
 * @uses \Translate\Controller\Admin\TranslateTermsController
 */
class TranslateTermsControllerTest extends IntegrationTestCase {

	/**
	 * Fixtures
	 *
	 * @var array<string>
	 */
	protected array $fixtures = [
		'plugin.Translate.TranslateProjects',
		'plugin.Translate.TranslateDomains',
		'plugin.Translate.TranslateTerms',
		'plugin.Translate.TranslateStrings',
		'plugin.Translate.TranslateLocales',
	];

	/**
	 * Test index method
	 *
	 * @return void
	 */
	public function testIndex() {
		$this->disableErrorHandlerMiddleware();
		$this->session(['TranslateProject.id' => 1]);

		$this->get(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateTerms', 'action' => 'index']);

		$this->assertResponseCode(200);
		$this->assertNoRedirect();
	}

	/**
	 * Test view method
	 *
	 * @return void
	 */
	public function testView() {
		$this->disableErrorHandlerMiddleware();
		$this->session(['TranslateProject.id' => 1]);

		$id = 1;
		$this->get(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateTerms', 'action' => 'view', $id]);

		$this->assertResponseCode(200);
		$this->assertNoRedirect();
	}

	/**
	 * Test edit method
	 *
	 * @return void
	 */
	public function testEdit() {
		$this->disableErrorHandlerMiddleware();
		$this->session(['TranslateProject.id' => 1]);

		$id = 1;
		$this->get(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateTerms', 'action' => 'edit', $id]);

		$this->assertResponseCode(200);
		$this->assertNoRedirect();
	}

	/**
	 * Test delete method
	 *
	 * @return void
	 */
	public function testDelete() {
		$this->disableErrorHandlerMiddleware();

		$id = 1;
		$this->post(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateTerms', 'action' => 'delete', $id]);

		$this->assertResponseCode(302);
		$this->assertRedirect();
	}

	/**
	 * Test pending method
	 *
	 * @return void
	 */
	public function testPending() {
		$this->disableErrorHandlerMiddleware();
		$this->session(['TranslateProject.id' => 1]);

		$this->get(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateTerms', 'action' => 'pending']);

		$this->assertResponseCode(200);
		$this->assertNoRedirect();
	}

	/**
	 * Test batchConfirm method POST
	 *
	 * @return void
	 */
	public function testBatchConfirmPost() {
		$this->disableErrorHandlerMiddleware();
		$this->session(['TranslateProject.id' => 1]);

		$this->post(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateTerms', 'action' => 'batchConfirm']);

		$this->assertResponseCode(302);
		$this->assertRedirect();
	}

	/**
	 * Test confirm method
	 *
	 * @return void
	 */
	public function testConfirm() {
		$this->disableErrorHandlerMiddleware();
		$this->session(['TranslateProject.id' => 1]);

		$id = 1;
		$this->post(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateTerms', 'action' => 'confirm', $id]);

		$this->assertResponseCode(302);
		$this->assertRedirect();
	}

}
