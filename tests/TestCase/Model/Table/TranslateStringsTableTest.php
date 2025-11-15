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
	 * @var array<string>
	 */
	protected array $fixtures = [
		'plugin.Translate.TranslateStrings',
		'plugin.Translate.TranslateTerms',
		'plugin.Translate.TranslateLocales',
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
		$result = $this->TranslateStrings->coverage(1);

		$this->assertIsArray($result);
	}

	/**
	 * Test getNext method
	 *
	 * @return void
	 */
	public function testGetNext() {
		// Create an untranslated string
		$data = [
			'name' => 'Untranslated String',
			'translate_domain_id' => 1,
			'skipped' => false,
		];
		$entity = $this->TranslateStrings->newEntity($data);
		$savedString = $this->TranslateStrings->save($entity);
		$this->assertNotFalse($savedString);

		// Now test getNext retrieves it
		$query = $this->TranslateStrings->getNext(null, $savedString->id);
		$this->assertInstanceOf('Cake\ORM\Query\SelectQuery', $query);

		$result = $query->first();
		$this->assertInstanceOf('Translate\Model\Entity\TranslateString', $result);
		$this->assertEquals('Untranslated String', $result->name);
		$this->assertEquals($savedString->id, $result->id);
	}

	/**
	 * Test plural validation with matching placeholders
	 *
	 * @return void
	 */
	public function testPluralValidationWithMatchingPlaceholders() {
		$data = [
			'name' => 'You have {0} item',
			'plural' => 'You have {0} items',
			'translate_domain_id' => 1,
		];
		$entity = $this->TranslateStrings->newEntity($data);
		$result = $this->TranslateStrings->save($entity);

		$this->assertNotFalse($result, print_r($entity->getErrors(), true));
	}

	/**
	 * Test plural validation fails with mismatched placeholders
	 *
	 * @return void
	 */
	public function testPluralValidationWithMismatchedPlaceholders() {
		$data = [
			'name' => 'You have {0} item',
			'plural' => 'You have items',
			'translate_domain_id' => 1,
		];
		$entity = $this->TranslateStrings->newEntity($data);
		$result = $this->TranslateStrings->save($entity);

		$this->assertFalse($result);
		$errors = $entity->getError('plural');
		$this->assertNotEmpty($errors);
		$errorMessage = is_array($errors) ? reset($errors) : $errors;
		$this->assertStringContainsString('placeholder', strtolower($errorMessage));
	}

	/**
	 * Test plural validation with multiple placeholders
	 *
	 * @return void
	 */
	public function testPluralValidationWithMultiplePlaceholders() {
		$data = [
			'name' => 'You have {0} items in {1} categories',
			'plural' => 'You have {0} items in {1} categories',
			'translate_domain_id' => 1,
		];
		$entity = $this->TranslateStrings->newEntity($data);
		$result = $this->TranslateStrings->save($entity);

		$this->assertNotFalse($result, print_r($entity->getErrors(), true));
	}

}
