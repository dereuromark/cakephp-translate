<?php
namespace Translate\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Translate\Model\Table\TranslateTermsTable;

/**
 * Translate\Model\Table\TranslateTermsTable Test Case
 */
class TranslateTermsTableTest extends TestCase {

	/**
	 * Test subject
	 *
	 * @var \Translate\Model\Table\TranslateTermsTable
	 */
	public $TranslateTerms;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'plugin.translate.translate_terms',
		'plugin.translate.translate_strings',
		'plugin.translate.translate_domains',
		'plugin.translate.translate_projects',
		'plugin.translate.translate_languages',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('TranslateTerms') ? [] : ['className' => 'Translate\Model\Table\TranslateTermsTable'];
		$this->TranslateTerms = TableRegistry::get('TranslateTerms', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->TranslateTerms);

		parent::tearDown();
	}

	/**
	 * @return void
	 */
	public function testInstance() {
		$this->assertInstanceOf(TranslateTermsTable::class, $this->TranslateTerms);
	}

	/**
	 * @return void
	 */
	public function testSave() {
		$data = [
			'content' => 'Foo Bar',
			'translate_string_id' => 1,
			'translate_language_id' => 1,
		];
		$entity = $this->TranslateTerms->newEntity($data);
		$result = $this->TranslateTerms->save($entity);

		$this->assertTrue((bool)$result, print_r($entity->errors(), true));
	}

	/**
	 * @return void
	 */
	public function testImport() {
		$translation = [
			'name' => 'Foo Bar',
			'content' => 'Meine Translation',
			'comment' => 'Foo Bar Comment',
		];
		$translateTerm = $this->TranslateTerms->import($translation, 1, 1);
		$this->assertNotNull($translateTerm);

		$this->assertEquals($translation['comment'], $translateTerm->comment);
	}

	/**
	 * Test getTranslations method
	 *
	 * @return void
	 */
	public function testGetTranslations() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test getTranslated method
	 *
	 * @return void
	 */
	public function testGetTranslated() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test process method
	 *
	 * @return void
	 */
	public function testProcess() {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
