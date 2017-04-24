<?php
namespace Translate\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Translate\Model\Table\TranslateLanguagesTable;

/**
 * Translate\Model\Table\TranslateLanguagesTable Test Case
 */
class TranslateLanguagesTableTest extends TestCase {

	/**
	 * Test subject
	 *
	 * @var \Translate\Model\Table\TranslateLanguagesTable
	 */
	public $TranslateLanguages;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'plugin.translate.translate_languages',
		//'plugin.translate.languages',
		'plugin.translate.translate_terms',
		'plugin.translate.translate_strings',
		'plugin.translate.translate_domains',
		'plugin.translate.translate_projects',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('TranslateLanguages') ? [] : ['className' => 'Translate\Model\Table\TranslateLanguagesTable'];
		$this->TranslateLanguages = TableRegistry::get('TranslateLanguages', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->TranslateLanguages);

		parent::tearDown();
	}

	/**
	 * @return void
	 */
	public function testInstance() {
		$this->assertInstanceOf(TranslateLanguagesTable::class, $this->TranslateLanguages);
	}

	/**
	 * @return void
	 */
	public function testSave() {
		$data = [
			'translate_project_id' => 1,
			'name' => 'Deutsch',
			'iso2' => 'DE',
			'locale' => 'de',
		];
		$entity = $this->TranslateLanguages->newEntity($data);
		$result = $this->TranslateLanguages->save($entity);

		$this->assertTrue((bool)$result);

		$entity = $this->TranslateLanguages->get($result->id);
		$this->assertSame('de', $entity->iso2);
	}

	/**
	 * Test getActive method
	 *
	 * @return void
	 */
	public function testGetActive() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test getList method
	 *
	 * @return void
	 */
	public function testGetList() {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
