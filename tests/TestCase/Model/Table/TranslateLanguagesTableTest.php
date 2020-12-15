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
	protected $fixtures = [
		'plugin.Translate.TranslateLanguages',
		'plugin.Translate.TranslateTerms',
		'plugin.Translate.TranslateStrings',
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
		$config = TableRegistry::exists('TranslateLanguages') ? [] : ['className' => 'Translate\Model\Table\TranslateLanguagesTable'];
		$this->TranslateLanguages = TableRegistry::getTableLocator()->get('TranslateLanguages', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown(): void {
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
	 * @return void
	 */
	public function testValidateIsoCode() {
		$result = $this->TranslateLanguages->validateIsoCode('de');
		$this->assertTrue($result);

		$result = $this->TranslateLanguages->validateIsoCode('deu');
		$this->assertFalse($result);
	}

	/**
	 * @return void
	 */
	public function testGetExtractableAsList() {
		$result = $this->TranslateLanguages->getExtractableAsList(1);
		$this->assertNotEmpty($result);
	}

	/**
	 * @return void
	 */
	public function testGetAsList() {
		$result = $this->TranslateLanguages->getAsList();
		$this->assertNotEmpty($result);
	}

	/**
	 * @return void
	 */
	public function testGetBaseLanguage() {
		$result = $this->TranslateLanguages->getBaseLanguage([]);
		$this->assertSame('en', $result);

		$result = $this->TranslateLanguages->getBaseLanguage($this->TranslateLanguages->find()->all()->toArray());
		$this->assertSame('Lo', $result);
	}

}
