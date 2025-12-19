<?php

namespace Translate\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Exception;
use Translate\Model\Entity\TranslateProject;
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
	 * @var array<string>
	 */
	protected array $fixtures = [
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
		$config = TableRegistry::getTableLocator()->exists('TranslateProjects') ? [] : ['className' => 'Translate\Model\Table\TranslateProjectsTable'];
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
		$result = $this->TranslateProjects->getDefaultProjectId();

		$this->assertIsInt($result);
		$this->assertEquals(1, $result);
	}

	/**
	 * Test reset method
	 *
	 * @return void
	 */
	public function testReset() {
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Invalid type');

		$this->TranslateProjects->reset(1, ['invalid_type'], [1]);
	}

	/**
	 * Test statuses method
	 *
	 * @return void
	 */
	public function testStatuses() {
		$result = TranslateProject::statuses();

		$this->assertIsArray($result);
		$this->assertNotEmpty($result);
		$this->assertArrayHasKey(0, $result);
		$this->assertArrayHasKey(1, $result);
		$this->assertArrayHasKey(2, $result);
	}

	/**
	 * Test types method
	 *
	 * @return void
	 */
	public function testTypes() {
		$result = TranslateProject::types();

		$this->assertIsArray($result);
		$this->assertNotEmpty($result);
		$this->assertArrayHasKey(0, $result);
		$this->assertArrayHasKey(1, $result);
		$this->assertArrayHasKey(9, $result);
	}

}
