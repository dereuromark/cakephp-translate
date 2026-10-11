<?php

namespace Translate\Test\TestCase\Controller\Admin;

use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use TestApp\Translator\Engine\Test;
use Translate\Test\TestCase\IntegrationTestCase;

class I18nEntriesControllerTest extends IntegrationTestCase {

	protected array $fixtures = [
		'plugin.Translate.TranslateProjects',
		'plugin.Translate.TranslateApiTranslations',
	];

	protected function setUp(): void {
		parent::setUp();
		Configure::write('Translate.engine', Test::class);
		$connection = ConnectionManager::get('test');
		$connection->execute('DROP TABLE IF EXISTS review_articles_translations');
		$connection->execute('DROP TABLE IF EXISTS review_articles');
		$connection->execute('CREATE TABLE review_articles (id INTEGER PRIMARY KEY, title VARCHAR(255), content TEXT)');
		$connection->execute('CREATE TABLE review_articles_translations (id INTEGER NOT NULL, locale VARCHAR(6) NOT NULL, title VARCHAR(255), content TEXT, auto BOOLEAN NOT NULL DEFAULT 0, PRIMARY KEY (id, locale))');
		$connection->insert('review_articles', ['id' => 1, 'title' => 'Title', 'content' => 'Content']);
		$connection->insert('review_articles_translations', ['id' => 1, 'locale' => 'de', 'title' => 'Manual title', 'content' => null, 'auto' => false]);
	}

	protected function tearDown(): void {
		$connection = ConnectionManager::get('test');
		$connection->execute('DROP TABLE IF EXISTS review_articles_translations');
		$connection->execute('DROP TABLE IF EXISTS review_articles');
		parent::tearDown();
	}

	public function testAutomaticTranslationPreservesManualFieldsAndClassification(): void {
		$this->post('/admin/translate/i18n-entries/auto-translate-record/review_articles_translations/1', ['locales' => ['de']]);
		$this->assertRedirect();
		$row = ConnectionManager::get('test')->execute('SELECT * FROM review_articles_translations WHERE id = 1')->fetch('assoc');
		$this->assertSame('Manual title', $row['title']);
		$this->assertSame('tnetnoC', $row['content']);
		$this->assertFalse((bool)$row['auto']);
	}

	public function testBatchTranslationPreservesManualFieldsAndClassification(): void {
		$this->post('/admin/translate/i18n-entries/auto-translate-batch/review_articles_translations', ['record_ids' => [1], 'locales' => ['de']]);
		$this->assertRedirect();
		$row = ConnectionManager::get('test')->execute('SELECT * FROM review_articles_translations WHERE id = 1')->fetch('assoc');
		$this->assertSame('Manual title', $row['title']);
		$this->assertSame('tnetnoC', $row['content']);
		$this->assertFalse((bool)$row['auto']);
	}

}
