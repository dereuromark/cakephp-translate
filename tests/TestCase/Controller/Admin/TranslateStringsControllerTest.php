<?php
namespace Translate\Test\TestCase\Controller\Admin;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestCase;

/**
 * Translate\Controller\Admin\TranslateStringsController Test Case
 */
class TranslateStringsControllerTest extends IntegrationTestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'plugin.translate.translate_strings',
		'plugin.translate.translate_groups',
		'plugin.translate.translate_languages',
		'plugin.translate.translate_projects',
		'plugin.translate.users',
	];

	/**
	 * Test index method
	 *
	 * @return void
	 */
	public function testIndex() {
		$this->get(['prefix' => 'admin', 'plugin' => 'Translate', 'controller' => 'TranslateStrings', 'action' => 'index']);

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
		$this->get(['prefix' => 'admin', 'plugin' => 'Translate', 'controller' => 'TranslateStrings', 'action' => 'view', $id]);

		$this->assertResponseCode(200);
		$this->assertNoRedirect();
	}

	/**
	 * Test view method
	 *
	 * @return void
	 */
	public function testExtract() {
		$this->get(['prefix' => 'admin', 'plugin' => 'Translate', 'controller' => 'TranslateStrings', 'action' => 'extract']);

		$this->assertResponseCode(200);
		$this->assertNoRedirect();
	}

	/**
	 * Test view method
	 *
	 * @return void
	 */
	public function testDump() {
		$this->get(['prefix' => 'admin', 'plugin' => 'Translate', 'controller' => 'TranslateStrings', 'action' => 'dump']);

		$this->assertResponseCode(200);
		$this->assertNoRedirect();
	}

	/**
	 * Test view method
	 *
	 * @return void
	 */
	public function testTranslate() {
		$id = 1;
		$this->TranslateStrings = TableRegistry::get('Translate.TranslateStrings');
		$record = $this->TranslateStrings->get($id);

		$groupId = $record->translate_group_id;
		$record = $this->TranslateStrings->TranslateGroups->get($groupId);

		$this->get(['prefix' => 'admin', 'plugin' => 'Translate', 'controller' => 'TranslateStrings', 'action' => 'translate', $id]);

		$this->assertResponseCode(200);
		$this->assertNoRedirect();
	}

	/**
	 * Test add method
	 *
	 * @return void
	 */
	public function testAdd() {
		$this->get(['prefix' => 'admin', 'plugin' => 'Translate', 'controller' => 'TranslateStrings', 'action' => 'add']);

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
		$this->get(['prefix' => 'admin', 'plugin' => 'Translate', 'controller' => 'TranslateStrings', 'action' => 'edit', $id]);

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
		$this->post(['prefix' => 'admin', 'plugin' => 'Translate', 'controller' => 'TranslateStrings', 'action' => 'delete', $id]);

		$this->assertResponseCode(302);
		$this->assertRedirect();
	}

}