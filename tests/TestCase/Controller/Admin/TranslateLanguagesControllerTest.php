<?php

namespace Translate\Test\TestCase\Controller\Admin;

use Shim\TestSuite\IntegrationTestCase;

/**
 * Translate\Controller\Admin\TranslateLanguagesController Test Case
 *
 * @uses \Translate\Controller\Admin\TranslateLanguagesController
 */
class TranslateLanguagesControllerTest extends IntegrationTestCase {

	/**
	 * Fixtures
	 *
	 * @var array<string>
	 */
	protected array $fixtures = [
		'plugin.Translate.TranslateLanguages',
		'plugin.Translate.TranslateTerms',
	];

	/**
	 * Test index method
	 *
	 * @return void
	 */
	public function testIndex() {
		$this->get(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateLanguages', 'action' => 'index']);

		$this->assertResponseCode(200);
		$this->assertNoRedirect();
	}

	/**
	 * Test view method
	 *
	 * @return void
	 */
	public function testView() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * @return void
	 */
	public function testFromLocale() {
		$this->get(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateLanguages', 'action' => 'fromLocale']);

		$this->assertResponseCode(200);
		$this->assertNoRedirect();
	}

	/**
	 * @return void
	 */
	public function testToLocale() {
		$this->get(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateLanguages', 'action' => 'toLocale']);

		$this->assertResponseCode(200);
		$this->assertNoRedirect();
	}

	/**
	 * @return void
	 */
	public function testAdd() {
		$this->get(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateLanguages', 'action' => 'add']);

		$this->assertResponseCode(200);
		$this->assertNoRedirect();
	}

	/**
	 * Test edit method
	 *
	 * @return void
	 */
	public function testEdit() {
		$id = 1;
		$this->get(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateLanguages', 'action' => 'edit', $id]);

		$this->assertResponseCode(200);
		$this->assertNoRedirect();
	}

	/**
	 * Test delete method
	 *
	 * @return void
	 */
	public function testDelete() {
		$id = 1;
		$this->post(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateLanguages', 'action' => 'delete', $id]);

		$this->assertResponseCode(302);
		$this->assertRedirect();
	}

}
