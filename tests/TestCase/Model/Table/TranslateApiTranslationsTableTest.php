<?php

namespace Translate\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Translate\Model\Entity\TranslateApiTranslation;

/**
 * Translate\Model\Table\TranslateApiTranslationsTable Test Case
 */
class TranslateApiTranslationsTableTest extends TestCase {

	/**
	 * Test subject
	 *
	 * @var \Translate\Model\Table\TranslateApiTranslationsTable
	 */
	public $TranslateApiTranslations;

	/**
	 * Fixtures
	 *
	 * @var array<string>
	 */
	protected array $fixtures = [
		'plugin.Translate.TranslateApiTranslations',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('TranslateApiTranslations') ? [] : ['className' => 'Translate\Model\Table\TranslateApiTranslationsTable'];
		$this->TranslateApiTranslations = TableRegistry::getTableLocator()->get('TranslateApiTranslations', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown(): void {
		unset($this->TranslateApiTranslations);

		parent::tearDown();
	}

	/**
	 * @return void
	 */
	public function testStore() {
		$result = $this->TranslateApiTranslations->store('k', 'v', 'en', 'de', 'e');

		$this->assertTrue((bool)$result);
	}

	/**
	 * @return void
	 */
	public function testRetrieve() {
		$result = $this->TranslateApiTranslations->retrieve('k', 'de', 'en', 'e');
		$this->assertNull($result);

		$translateApiTranslation = $this->TranslateApiTranslations->newEntity([
			'key' => 'k',
			'value' => 'v',
			'engine' => 'e',
			'from' => 'en',
			'to' => 'de',
		]);
		$this->TranslateApiTranslations->save($translateApiTranslation);

		$result = $this->TranslateApiTranslations->retrieve('k', 'de', 'en', 'e');
		$this->assertInstanceOf(TranslateApiTranslation::class, $result);
	}

	/**
	 * @return void
	 */
	public function testRetrieveAnyEngine() {
		$result = $this->TranslateApiTranslations->retrieve('k', 'de', 'en');
		$this->assertNull($result);

		$translateApiTranslation = $this->TranslateApiTranslations->newEntity([
			'key' => 'k',
			'value' => 'v',
			'engine' => 'e',
			'from' => 'en',
			'to' => 'de',
		]);
		$this->TranslateApiTranslations->save($translateApiTranslation);

		$result = $this->TranslateApiTranslations->retrieve('k', 'de', 'en');
		$this->assertInstanceOf(TranslateApiTranslation::class, $result);
	}

}
