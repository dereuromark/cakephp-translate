<?php

namespace Translate\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Translate\Model\Table\TranslateStringsTable;

/**
 * Translate\Model\Table\TranslateStringsTable Test Case
 */
class TranslateStringsTableTest extends TestCase {

	/**
	 * Test subject
	 *
	 * @var \Translate\Model\Table\TranslateStringsTable
	 */
	public $TranslateStrings;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	protected array $fixtures = [
		'plugin.Translate.TranslateStrings',
		'plugin.Translate.TranslateTerms',
		'plugin.Translate.TranslateLanguages',
		'plugin.Translate.TranslateDomains',
		'plugin.Translate.TranslateProjects',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('TranslateStrings') ? [] : ['className' => 'Translate\Model\Table\TranslateStringsTable'];
		$this->TranslateStrings = TableRegistry::getTableLocator()->get('TranslateStrings', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown(): void {
		unset($this->TranslateStrings);

		parent::tearDown();
	}

	/**
	 * @return void
	 */
	public function testInstance() {
		$this->assertInstanceOf(TranslateStringsTable::class, $this->TranslateStrings);
	}

	/**
	 * @return void
	 */
	public function testSave() {
		$data = [
			'name' => 'Foo Bar',
			'translate_project_id' => 1,
			'translate_domain_id' => 1,
		];
		$entity = $this->TranslateStrings->newEntity($data);
		$result = $this->TranslateStrings->save($entity);

		$this->assertTrue((bool)$result);
	}

	/**
	 * @return void
	 */
	public function testImport() {
		$translation = [
			'name' => 'Foo Bar',
			'plural' => 'Foo Bars',
			'context' => 'My context',
		];
		$translateString = $this->TranslateStrings->import($translation, 1);
		$this->assertNotNull($translateString);

		$this->assertFalse($translateString->is_html);
	}

	/**
	 * @return void
	 */
	public function testImportHtml() {
		$translation = [
			'name' => 'Foo Bar <b>bold</b>',
			'plural' => 'Foo Bars',
		];
		$translateString = $this->TranslateStrings->import($translation, 1);
		$this->assertNotNull($translateString);

		$this->assertTrue($translateString->is_html);
	}

	/**
	 * Test coverage method
	 *
	 * @return void
	 */
	public function testCoverage() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test getNext method
	 *
	 * @return void
	 */
	public function testGetNext() {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
