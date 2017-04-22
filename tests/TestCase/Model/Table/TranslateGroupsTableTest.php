<?php
namespace Translate\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Translate\Model\Table\TranslateGroupsTable;

/**
 * Translate\Model\Table\TranslateGroupsTable Test Case
 */
class TranslateGroupsTableTest extends TestCase {

	/**
	 * Test subject
	 *
	 * @var \Translate\Model\Table\TranslateGroupsTable
	 */
	public $TranslateGroups;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'plugin.translate.translate_groups',
		'plugin.translate.translate_projects',
		'plugin.translate.translate_strings',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('TranslateGroups') ? [] : ['className' => 'Translate\Model\Table\TranslateGroupsTable'];
		$this->TranslateGroups = TableRegistry::get('TranslateGroups', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->TranslateGroups);

		parent::tearDown();
	}

	/**
	 * @return void
	 */
	public function testInstance() {
		$this->assertInstanceOf(TranslateGroupsTable::class, $this->TranslateGroups);
	}

	/**
	 * @return void
	 */
	public function testSave() {
		$data = [
			'name' => 'default',
			'translate_project_id' => 1,
		];
		$entity = $this->TranslateGroups->newEntity($data);
		$result = $this->TranslateGroups->save($entity);

		$this->assertTrue((bool)$result);
	}

	/**
	 * Test statistics method
	 *
	 * @return void
	 */
	public function testStatistics() {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
