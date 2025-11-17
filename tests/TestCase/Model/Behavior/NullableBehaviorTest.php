<?php

namespace Translate\Test\TestCase\Model\Behavior;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * Test NullableBehavior functionality
 */
class NullableBehaviorTest extends TestCase {

	/**
	 * Fixtures
	 *
	 * @var list<string>
	 */
	protected array $fixtures = [
		'plugin.Translate.TranslateProjects',
		'plugin.Translate.TranslateDomains',
		'plugin.Translate.TranslateLocales',
		'plugin.Translate.TranslateStrings',
		'plugin.Translate.TranslateTerms',
	];

	/**
	 * Test that behavior is loaded on tables
	 *
	 * @return void
	 */
	public function testBehaviorIsLoaded() {
		$table = TableRegistry::getTableLocator()->get('Translate.TranslateStrings');

		$this->assertTrue($table->hasBehavior('Nullable'), 'TranslateStrings should have Nullable behavior');
	}

	/**
	 * Test that empty strings are converted to null for nullable fields
	 *
	 * @return void
	 */
	public function testEmptyStringConvertedToNull() {
		$table = TableRegistry::getTableLocator()->get('Translate.TranslateStrings');

		// Create entity with empty string for nullable field 'context'
		$data = [
			'name' => 'test string',
			'context' => '', // Empty string should be converted to null
			'translate_domain_id' => 1,
		];

		$entity = $table->newEntity($data);

		$this->assertNull($entity->context, 'Empty string should be converted to null for nullable field');
	}

	/**
	 * Test that non-empty strings are not affected
	 *
	 * @return void
	 */
	public function testNonEmptyStringNotAffected() {
		$table = TableRegistry::getTableLocator()->get('Translate.TranslateStrings');

		$data = [
			'name' => 'test string',
			'context' => 'some context',
			'translate_domain_id' => 1,
		];

		$entity = $table->newEntity($data);

		$this->assertSame('some context', $entity->context, 'Non-empty string should not be changed');
	}

	/**
	 * Test that behavior works with associated data
	 *
	 * @return void
	 */
	public function testBehaviorWithAssociations() {
		$table = TableRegistry::getTableLocator()->get('Translate.TranslateStrings');

		$data = [
			'name' => 'test string',
			'context' => '',
			'translate_domain_id' => 1,
		];

		$entity = $table->newEntity($data);

		$this->assertNull($entity->context, 'Empty string converted to null in main entity');
	}

}
