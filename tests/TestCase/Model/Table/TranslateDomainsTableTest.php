<?php

namespace Translate\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Translate\Model\Table\TranslateDomainsTable;

/**
 * Translate\Model\Table\TranslateDomainsTable Test Case
 */
class TranslateDomainsTableTest extends TestCase {

	/**
	 * Test subject
	 *
	 * @var \Translate\Model\Table\TranslateDomainsTable
	 */
	public $TranslateDomains;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	protected array $fixtures = [
		'plugin.Translate.TranslateDomains',
		'plugin.Translate.TranslateProjects',
		'plugin.Translate.TranslateStrings',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('TranslateDomains') ? [] : ['className' => 'Translate\Model\Table\TranslateDomainsTable'];
		$this->TranslateDomains = TableRegistry::getTableLocator()->get('TranslateDomains', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown(): void {
		unset($this->TranslateDomains);

		parent::tearDown();
	}

	/**
	 * @return void
	 */
	public function testInstance() {
		$this->assertInstanceOf(TranslateDomainsTable::class, $this->TranslateDomains);
	}

	/**
	 * @return void
	 */
	public function testSave() {
		$data = [
			'name' => 'default',
			'translate_project_id' => 1,
		];
		$entity = $this->TranslateDomains->newEntity($data);
		$result = $this->TranslateDomains->save($entity);

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
