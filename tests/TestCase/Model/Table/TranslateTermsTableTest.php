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
	 * @var array<string>
	 */
	protected array $fixtures = [
		'plugin.Translate.TranslateTerms',
		'plugin.Translate.TranslateStrings',
		'plugin.Translate.TranslateDomains',
		'plugin.Translate.TranslateProjects',
		'plugin.Translate.TranslateLanguages',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('TranslateTerms') ? [] : ['className' => 'Translate\Model\Table\TranslateTermsTable'];
		$this->TranslateTerms = TableRegistry::getTableLocator()->get('TranslateTerms', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown(): void {
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

		$this->assertTrue((bool)$result, print_r($entity->getErrors(), true));
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
		$query = $this->TranslateTerms->getTranslations(1);
		$this->assertInstanceOf('Cake\ORM\Query\SelectQuery', $query);

		$results = $query->toArray();
		$this->assertNotEmpty($results);
	}

	/**
	 * Test getTranslated method
	 *
	 * @return void
	 */
	public function testGetTranslated() {
		$query = $this->TranslateTerms->getTranslated(1);
		$this->assertInstanceOf('Cake\ORM\Query\SelectQuery', $query);

		$results = $query->toArray();
		$this->assertNotEmpty($results);
	}

	/**
	 * Test process method
	 *
	 * @return void
	 */
	public function testProcess() {
		$data = [
			'content' => 'Test Translation',
			'translate_string_id' => 1,
			'translate_language_id' => 1,
		];
		$result = $this->TranslateTerms->process($data);

		$this->assertTrue((bool)$result);
		$this->assertEquals('Test Translation', $result->content);
	}

	/**
	 * Test saving with plural_2
	 *
	 * @return void
	 */
	public function testSaveWithPlural() {
		$data = [
			'content' => 'One item',
			'plural_2' => 'Multiple items',
			'translate_string_id' => 1,
			'translate_language_id' => 1,
		];
		$entity = $this->TranslateTerms->newEntity($data);
		$result = $this->TranslateTerms->save($entity);

		$this->assertTrue((bool)$result, print_r($entity->getErrors(), true));
		$this->assertEquals('One item', $result->content);
		$this->assertEquals('Multiple items', $result->plural_2);
	}

	/**
	 * Test placeholder validation for singular
	 *
	 * @return void
	 */
	public function testValidatePlaceholdersSingular() {
		$data = [
			'content' => 'You have {0} items',
			'string' => 'You have {0} apples',
			'translate_string_id' => 1,
			'translate_language_id' => 1,
		];
		$entity = $this->TranslateTerms->newEntity($data);
		$result = $this->TranslateTerms->save($entity);

		$this->assertTrue((bool)$result, print_r($entity->getErrors(), true));
	}

	/**
	 * Test placeholder validation fails when placeholders don't match
	 *
	 * @return void
	 */
	public function testValidatePlaceholdersMismatch() {
		$data = [
			'content' => 'You have items',
			'string' => 'You have {0} apples',
			'translate_string_id' => 1,
			'translate_language_id' => 1,
		];
		$entity = $this->TranslateTerms->newEntity($data);
		$result = $this->TranslateTerms->save($entity);

		$this->assertFalse($result);
		$this->assertNotEmpty($entity->getError('content'));
	}

	/**
	 * Test placeholder validation for plural with same placeholders
	 *
	 * @return void
	 */
	public function testValidatePlaceholdersPluralMatch() {
		$data = [
			'content' => 'You have {0} item',
			'plural_2' => 'You have {0} items',
			'string' => 'You have {0} apples',
			'translate_string_id' => 1,
			'translate_language_id' => 1,
		];
		$entity = $this->TranslateTerms->newEntity($data);
		$result = $this->TranslateTerms->save($entity);

		$this->assertTrue((bool)$result, print_r($entity->getErrors(), true));
	}

	/**
	 * Test placeholder validation for plural fails when plural placeholders don't match
	 *
	 * @return void
	 */
	public function testValidatePlaceholdersPluralMismatch() {
		$data = [
			'content' => 'You have {0} item',
			'plural_2' => 'You have items',
			'string' => 'You have {0} apples',
			'translate_string_id' => 1,
			'translate_language_id' => 1,
		];
		$entity = $this->TranslateTerms->newEntity($data);
		$result = $this->TranslateTerms->save($entity);

		$this->assertFalse($result);
		$this->assertNotEmpty($entity->getError('plural_2'));
	}

	/**
	 * Test multiple placeholders validation
	 *
	 * @return void
	 */
	public function testValidatePlaceholdersMultiple() {
		$data = [
			'content' => 'You have {0} items in {1} categories',
			'string' => 'You have {0} apples in {1} baskets',
			'translate_string_id' => 1,
			'translate_language_id' => 1,
		];
		$entity = $this->TranslateTerms->newEntity($data);
		$result = $this->TranslateTerms->save($entity);

		$this->assertTrue((bool)$result, print_r($entity->getErrors(), true));
	}

	/**
	 * Test import with plural
	 *
	 * @return void
	 */
	public function testImportWithPlural() {
		$translation = [
			'name' => 'One apple',
			'plural' => 'Multiple apples',
			'content' => 'Ein Apfel',
			'plural_2' => 'Mehrere Äpfel',
		];
		$translateTerm = $this->TranslateTerms->import($translation, 1, 1);
		$this->assertNotNull($translateTerm);

		$this->assertEquals('Ein Apfel', $translateTerm->content);
		$this->assertEquals('Mehrere Äpfel', $translateTerm->plural_2);
	}

}
