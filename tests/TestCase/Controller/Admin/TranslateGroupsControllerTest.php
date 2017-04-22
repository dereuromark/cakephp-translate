<?php
namespace Translate\Test\TestCase\Controller\Admin;

use Cake\TestSuite\IntegrationTestCase;

/**
 * Translate\Controller\Admin\TranslateGroupsController Test Case
 */
class TranslateGroupsControllerTest extends IntegrationTestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'plugin.translate.translate_groups',
		'plugin.translate.translate_strings',
		'plugin.translate.translate_projects',
	];

	/**
	 * Test index method
	 *
	 * @return void
	 */
	public function testIndex() {
		$this->get(['prefix' => 'admin', 'plugin' => 'Translate', 'controller' => 'TranslateGroups', 'action' => 'index']);

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
		$this->get(['prefix' => 'admin', 'plugin' => 'Translate', 'controller' => 'TranslateGroups', 'action' => 'view', $id]);

		$this->assertResponseCode(200);
		$this->assertNoRedirect();
	}

	/**
	 * Test add method
	 *
	 * @return void
	 */
	public function testAdd() {
		$this->get(['prefix' => 'admin', 'plugin' => 'Translate', 'controller' => 'TranslateGroups', 'action' => 'add']);

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
		$this->get(['prefix' => 'admin', 'plugin' => 'Translate', 'controller' => 'TranslateGroups', 'action' => 'edit', $id]);

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
		$this->post(['prefix' => 'admin', 'plugin' => 'Translate', 'controller' => 'TranslateGroups', 'action' => 'delete', $id]);

		$this->assertResponseCode(302);
		$this->assertRedirect();
	}

}
