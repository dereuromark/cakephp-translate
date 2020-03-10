<?php

namespace Translate\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Translate\Model\Table\TranslateProjectsTable;

/**
 * Translate\Model\Table\TranslateProjectsTable Test Case
 */
class TranslateProjectsTableTest extends TestCase {

	/**
	 * Test subject
	 *
	 * @var \Translate\Model\Table\TranslateProjectsTable
	 */
	public $TranslateProjects;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	protected $fixtures = [
		'plugin.Translate.TranslateProjects',
		'plugin.Translate.TranslateDomains',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::exists('TranslateProjects') ? [] : ['className' => 'Translate\Model\Table\TranslateProjectsTable'];
		$this->TranslateProjects = TableRegistry::getTableLocator()->get('TranslateProjects', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown(): void {
		unset($this->TranslateProjects);

		parent::tearDown();
	}

	/**
	 * @return void
	 */
	public function testInstance() {
		$this->assertInstanceOf(TranslateProjectsTable::class, $this->TranslateProjects);
	}

	/**
	 * @return void
	 */
	public function testSave() {
		$data = [
			'name' => 'Main',
		];
		$entity = $this->TranslateProjects->newEntity($data);
		$result = $this->TranslateProjects->save($entity);

		$this->assertTrue((bool)$result);
	}

	/**
	 * Test getDefaultProjectId method
	 *
	 * @return void
	 */
	public function testGetDefaultProjectId() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test reset method
	 *
	 * @return void
	 */
	public function testReset() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test statuses method
	 *
	 * @return void
	 */
	public function testStatuses() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test types method
	 *
	 * @return void
	 */
	public function testTypes() {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
