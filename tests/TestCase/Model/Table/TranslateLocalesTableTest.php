<?php

namespace Translate\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Translate\Model\Table\TranslateLocalesTable;

/**
 * Translate\Model\Table\TranslateLocalesTable Test Case
 */
class TranslateLocalesTableTest extends TestCase {

	/**
	 * Test subject
	 *
	 * @var \Translate\Model\Table\TranslateLocalesTable
	 */
	public $TranslateLocales;

	/**
	 * Fixtures
	 *
	 * @var array<string>
	 */
	protected array $fixtures = [
		'plugin.Translate.TranslateLocales',
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
		$config = TableRegistry::getTableLocator()->exists('TranslateLocales') ? [] : ['className' => 'Translate\Model\Table\TranslateLocalesTable'];
		$this->TranslateLocales = TableRegistry::getTableLocator()->get('TranslateLocales', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown(): void {
		unset($this->TranslateLocales);

		parent::tearDown();
	}

	/**
	 * @return void
	 */
	public function testInstance() {
		$this->assertInstanceOf(TranslateLocalesTable::class, $this->TranslateLocales);
	}

	/**
	 * @return void
	 */
	public function testSave() {
		$data = [
			'translate_project_id' => 1,
			'name' => 'Deutsch',
			'iso2' => 'DE',
			'locale' => 'DE_DE',
		];
		$entity = $this->TranslateLocales->newEntity($data);
		$result = $this->TranslateLocales->save($entity);

		$this->assertTrue((bool)$result);

		$entity = $this->TranslateLocales->get($result->id);
		$this->assertSame('de_DE', $entity->locale);
		$this->assertSame('de', $entity->iso2);
	}

	/**
	 * @return void
	 */
	public function testValidateIsoCode() {
		$result = $this->TranslateLocales->validateIsoCode('de');
		$this->assertTrue($result);

		$result = $this->TranslateLocales->validateIsoCode('deu');
		$this->assertFalse($result);
	}

	/**
	 * @return void
	 */
	public function testGetExtractableAsList() {
		$result = $this->TranslateLocales->getExtractableAsList(1);
		$this->assertNotEmpty($result);
	}

	/**
	 * @return void
	 */
	public function testGetAsList() {
		$result = $this->TranslateLocales->getAsList();
		$this->assertNotEmpty($result);
	}

	/**
	 * @return void
	 */
	public function testGetBaseLocale() {
		$result = $this->TranslateLocales->getBaseLocale([]);
		$this->assertSame('en', $result);

		$result = $this->TranslateLocales->getBaseLocale($this->TranslateLocales->find()->all()->toArray());
		$this->assertSame('en_US', $result);
	}

}
