<?php

namespace Translate\Test\TestCase\Controller\Admin;

use Cake\Datasource\ConnectionManager;
use Translate\Test\TestCase\IntegrationTestCase;

/**
 * Translate\Controller\Admin\TranslateBehaviorController Test Case
 *
 * @uses \Translate\Controller\Admin\TranslateBehaviorController
 */
class TranslateBehaviorControllerTest extends IntegrationTestCase {

	/**
	 * Fixtures
	 *
	 * @var array<string>
	 */
	protected array $fixtures = [
		'plugin.Translate.TranslateProjects',
		'plugin.Translate.TranslateLocales',
		'plugin.Translate.TranslateDomains',
		'plugin.Translate.TranslateStrings',
		'plugin.Translate.TranslateTerms',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		// Set a default project in session
		$this->session(['Translation.currentProjectId' => 1]);

		// Enable flash message retention for testing
		$this->enableRetainFlashMessages();
	}

	/**
	 * Test index method
	 *
	 * @return void
	 */
	public function testIndex() {
		$this->get(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateBehavior', 'action' => 'index']);

		$this->assertResponseCode(200);
		$this->assertNoRedirect();

		// Check that view variables are set (can be empty arrays in test environment)
		$this->assertIsArray($this->viewVariable('shadowTables'));
		$this->assertIsArray($this->viewVariable('orphanedShadowTables'));
		$this->assertIsArray($this->viewVariable('modelsWithBehavior'));
		$this->assertIsArray($this->viewVariable('candidateTables'));
		$this->assertIsArray($this->viewVariable('translationStrategies'));
	}

	/**
	 * Test view method with valid shadow table
	 *
	 * @return void
	 */
	public function testViewValidShadowTable() {
		// First, create a test shadow table
		$connection = ConnectionManager::get('test');
		$connection->execute('DROP TABLE IF EXISTS test_articles_i18n');
		$connection->execute('
			CREATE TABLE test_articles_i18n (
				id INTEGER PRIMARY KEY AUTOINCREMENT,
				locale VARCHAR(6) NOT NULL,
				model VARCHAR(255) NOT NULL,
				foreign_key INTEGER NOT NULL,
				field VARCHAR(255) NOT NULL,
				content TEXT
			)
		');
		$connection->execute('CREATE INDEX idx_locale ON test_articles_i18n (locale)');
		$connection->execute('CREATE UNIQUE INDEX idx_model_foreign_key_locale_field ON test_articles_i18n (model, foreign_key, locale, field)');

		$this->get(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateBehavior', 'action' => 'view', 'test_articles_i18n']);

		$this->assertResponseCode(200);
		$this->assertNoRedirect();

		// Check view variables
		$this->assertEquals('test_articles_i18n', $this->viewVariable('tableName'));
		$this->assertEquals('test_articles', $this->viewVariable('baseTableName'));
		$this->assertEquals('eav', $this->viewVariable('strategy'));

		// Cleanup
		$connection->execute('DROP TABLE IF EXISTS test_articles_i18n');
	}

	/**
	 * Test view method with invalid table name
	 *
	 * @return void
	 */
	public function testViewInvalidTableName() {
		$this->get(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateBehavior', 'action' => 'view', 'invalid_table']);

		$this->assertResponseCode(302);
		$this->assertRedirect(['action' => 'index']);
		$this->assertFlashMessage('Invalid shadow table name');
	}

	/**
	 * Test view method with non-shadow table
	 *
	 * @return void
	 */
	public function testViewNonShadowTable() {
		$this->get(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateBehavior', 'action' => 'view', 'translate_projects']);

		$this->assertResponseCode(302);
		$this->assertRedirect(['action' => 'index']);
		$this->assertFlashMessage('Invalid shadow table name');
	}

	/**
	 * Test generate method without table name
	 *
	 * @return void
	 */
	public function testGenerateWithoutTableName() {
		$this->get(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateBehavior', 'action' => 'generate']);

		$this->assertResponseCode(200);
		$this->assertNoRedirect();

		// Should show list of available tables
		$this->assertNotEmpty($this->viewVariable('availableTables'));
	}

	/**
	 * Test generate method with table name (GET)
	 *
	 * @return void
	 */
	public function testGenerateWithTableNameGet() {
		// Create a test table with text fields
		$connection = ConnectionManager::get('test');
		$connection->execute('DROP TABLE IF EXISTS test_articles');
		$connection->execute('
			CREATE TABLE test_articles (
				id INTEGER PRIMARY KEY AUTOINCREMENT,
				title VARCHAR(255) NOT NULL,
				body TEXT,
				created DATETIME,
				modified DATETIME
			)
		');

		$this->get(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateBehavior', 'action' => 'generate', 'test_articles']);

		$this->assertResponseCode(200);
		$this->assertNoRedirect();

		// Check view variables
		$this->assertEquals('test_articles', $this->viewVariable('tableName'));
		$this->assertNotEmpty($this->viewVariable('translatableFields'));
		$this->assertNull($this->viewVariable('migrationCode'));
		$this->assertEquals('shadow_table', $this->viewVariable('strategy'));

		// Cleanup
		$connection->execute('DROP TABLE IF EXISTS test_articles');
	}

	/**
	 * Test generate method with POST data (Shadow Table strategy)
	 *
	 * @return void
	 */
	public function testGenerateWithPostShadowTable() {
		// Create a test table with text fields
		$connection = ConnectionManager::get('test');
		$connection->execute('DROP TABLE IF EXISTS test_posts');
		$connection->execute('
			CREATE TABLE test_posts (
				id INTEGER PRIMARY KEY AUTOINCREMENT,
				title VARCHAR(255) NOT NULL,
				content TEXT,
				created DATETIME
			)
		');

		$this->post(
			['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateBehavior', 'action' => 'generate', 'test_posts'],
			[
				'fields' => ['title', 'content'],
				'strategy' => 'shadow_table',
			],
		);

		$this->assertResponseCode(200);
		$this->assertNoRedirect();

		// Check that migration code was generated
		$migrationCode = $this->viewVariable('migrationCode');
		$this->assertNotEmpty($migrationCode);
		$this->assertStringContainsString('test_posts_i18n', $migrationCode);
		$this->assertStringContainsString('AddI18nForTestPosts', $migrationCode);
		$this->assertStringContainsString("'title'", $migrationCode);
		$this->assertStringContainsString("'content'", $migrationCode);
		$this->assertStringContainsString('Shadow Table strategy', $migrationCode);

		$this->assertEquals('AddI18nForTestPosts', $this->viewVariable('migrationName'));

		// Cleanup
		$connection->execute('DROP TABLE IF EXISTS test_posts');
	}

	/**
	 * Test generate method with POST data (EAV strategy)
	 *
	 * @return void
	 */
	public function testGenerateWithPostEav() {
		// Create a test table with text fields
		$connection = ConnectionManager::get('test');
		$connection->execute('DROP TABLE IF EXISTS test_products');
		$connection->execute('
			CREATE TABLE test_products (
				id INTEGER PRIMARY KEY AUTOINCREMENT,
				name VARCHAR(255) NOT NULL,
				description TEXT,
				created DATETIME
			)
		');

		$this->post(
			['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateBehavior', 'action' => 'generate', 'test_products'],
			[
				'fields' => ['name', 'description'],
				'strategy' => 'eav',
			],
		);

		$this->assertResponseCode(200);
		$this->assertNoRedirect();

		// Check that migration code was generated with EAV strategy
		$migrationCode = $this->viewVariable('migrationCode');
		$this->assertNotEmpty($migrationCode);
		$this->assertStringContainsString('test_products_i18n', $migrationCode);
		$this->assertStringContainsString('EAV strategy', $migrationCode);
		$this->assertStringContainsString('locale', $migrationCode);
		$this->assertStringContainsString('model', $migrationCode);
		$this->assertStringContainsString('foreign_key', $migrationCode);
		$this->assertStringContainsString('field', $migrationCode);
		$this->assertStringContainsString('content', $migrationCode);

		// Cleanup
		$connection->execute('DROP TABLE IF EXISTS test_products');
	}

	/**
	 * Test generate method without selecting fields
	 *
	 * @return void
	 */
	public function testGenerateWithoutFields() {
		// Create a test table
		$connection = ConnectionManager::get('test');
		$connection->execute('DROP TABLE IF EXISTS test_items');
		$connection->execute('
			CREATE TABLE test_items (
				id INTEGER PRIMARY KEY AUTOINCREMENT,
				title VARCHAR(255) NOT NULL
			)
		');

		$this->post(
			['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateBehavior', 'action' => 'generate', 'test_items'],
			[
				'fields' => [],
				'strategy' => 'shadow_table',
			],
		);

		$this->assertResponseCode(200);
		$this->assertFlashMessage('Please select at least one field to translate', 'flash');

		// Migration code should not be generated
		$this->assertNull($this->viewVariable('migrationCode'));

		// Cleanup
		$connection->execute('DROP TABLE IF EXISTS test_items');
	}

	/**
	 * Test generate method with non-existent table
	 *
	 * @return void
	 */
	public function testGenerateNonExistentTable() {
		$this->get(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateBehavior', 'action' => 'generate', 'non_existent_table']);

		$this->assertResponseCode(302);
		$this->assertRedirect(['action' => 'generate']);
		$this->assertFlashMessage('Table non_existent_table not found');
	}

	/**
	 * Test save migration action
	 *
	 * @return void
	 */
	public function testSaveMigration() {
		$migrationCode = '<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddI18nForTestTable extends BaseMigration
{
    public function change(): void
    {
        // Migration code here
    }
}';

		$migrationPath = ROOT . DS . 'config' . DS . 'Migrations';
		if (!is_dir($migrationPath)) {
			mkdir($migrationPath, 0755, true);
		}

		$this->post(
			['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateBehavior', 'action' => 'saveMigration'],
			[
				'table_name' => 'test_table',
				'migration_name' => 'AddI18nForTestTable',
				'migration_code' => $migrationCode,
			],
		);

		$this->assertResponseCode(302);
		$this->assertRedirect(['action' => 'generate', 'test_table']);
		$this->assertFlashElement('flash/success');

		// Check that the flash message contains the expected text
		$session = $this->_requestSession;
		$flash = $session->read('Flash.flash');
		$this->assertNotEmpty($flash);
		$this->assertStringContainsString('Migration file created successfully', $flash[0]['message']);

		// Cleanup - find and delete the created migration file
		$files = glob($migrationPath . DS . '*_AddI18nForTestTable.php');
		foreach ($files as $file) {
			if (file_exists($file)) {
				unlink($file);
			}
		}
	}

	/**
	 * Test save migration with missing data
	 *
	 * @return void
	 */
	public function testSaveMigrationMissingData() {
		$this->post(
			['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateBehavior', 'action' => 'saveMigration'],
			[
				'table_name' => 'test_table',
			],
		);

		$this->assertResponseCode(302);
		$this->assertFlashMessage('Missing required data');
	}

}
