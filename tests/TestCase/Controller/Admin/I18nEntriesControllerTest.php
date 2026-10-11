<?php

namespace Translate\Test\TestCase\Controller\Admin;

use Cake\Controller\Exception\FormProtectionException;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Datasource\ConnectionManager;
use Cake\Event\EventInterface;
use Cake\Event\EventManager;
use Cake\Form\FormProtector;
use Cake\ORM\Behavior\Translate\ShadowTableStrategy;
use Cake\ORM\TableRegistry;
use Cake\Utility\Security;
use TestApp\Translator\Engine\Test;
use Tools\ToolsPlugin;
use Translate\Test\TestCase\IntegrationTestCase;

class I18nEntriesControllerTest extends IntegrationTestCase {

	protected array $fixtures = [
		'plugin.Translate.TranslateProjects',
		'plugin.Translate.TranslateApiTranslations',
	];

	protected function setUp(): void {
		parent::setUp();
		Configure::write('Translate.engine', Test::class);
		EventManager::instance()->on('Controller.initialize', function (): void {
			if (!Plugin::isLoaded('Tools')) {
				Plugin::getCollection()->add(new ToolsPlugin());
			}
		});
		$connection = ConnectionManager::get('test');
		$connection->execute('DROP TABLE IF EXISTS review_orphans_translations');
		$connection->execute('DROP TABLE IF EXISTS review_articles_i18n');
		$connection->execute('DROP TABLE IF EXISTS review_articles_translations');
		$connection->execute('DROP TABLE IF EXISTS review_articles');
		$connection->execute('CREATE TABLE review_articles (id INTEGER PRIMARY KEY, title VARCHAR(255), content TEXT)');
		$connection->execute('CREATE TABLE review_articles_translations (id INTEGER NOT NULL, locale VARCHAR(6) NOT NULL, title VARCHAR(255), content TEXT, auto BOOLEAN NOT NULL DEFAULT 0, PRIMARY KEY (id, locale))');
		$connection->execute('CREATE TABLE review_orphans_translations (id INTEGER NOT NULL, locale VARCHAR(6) NOT NULL, title VARCHAR(255), auto BOOLEAN NOT NULL DEFAULT 0, PRIMARY KEY (id, locale))');
		$connection->execute('CREATE TABLE review_articles_i18n (id INTEGER PRIMARY KEY, locale VARCHAR(6), model VARCHAR(100), foreign_key INTEGER, field VARCHAR(100), content TEXT, auto BOOLEAN)');
		$connection->insert('review_orphans_translations', ['id' => 1, 'locale' => 'de', 'title' => 'German', 'auto' => 1]);
		$connection->insert('review_orphans_translations', ['id' => 1, 'locale' => 'fr', 'title' => 'French', 'auto' => 1]);
		$connection->insert('review_articles', ['id' => 1, 'title' => 'Title', 'content' => 'Content']);
		$connection->insert('review_articles_translations', ['id' => 1, 'locale' => 'de', 'title' => 'Manual title', 'content' => null, 'auto' => 0]);
	}

	protected function tearDown(): void {
		$connection = ConnectionManager::get('test');
		$connection->execute('DROP TABLE IF EXISTS review_orphans_translations');
		$connection->execute('DROP TABLE IF EXISTS review_articles_i18n');
		$connection->execute('DROP TABLE IF EXISTS review_articles_translations');
		$connection->execute('DROP TABLE IF EXISTS review_articles');
		Configure::delete('Translate.engine');
		Configure::write('debug', true);
		EventManager::instance()->off('Controller.initialize');
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

	public function testOrphanShadowViewIncludesLocaleLinks(): void {
		$this->get('/admin/translate/i18n-entries/view/review_orphans_translations/1/de');
		$this->assertResponseOk();
		$this->assertSame('German', $this->viewVariable('entry')->get('title'));
		$this->assertResponseContains('/edit/review_orphans_translations/1/de');
	}

	public function testOrphanShadowEditChangesOnlySelectedLocale(): void {
		$this->post('/admin/translate/i18n-entries/edit/review_orphans_translations/1/de', ['title' => 'Changed']);
		$this->assertRedirect();
		$connection = ConnectionManager::get('test');
		$this->assertSame('Changed', $connection->execute("SELECT title FROM review_orphans_translations WHERE locale = 'de'")->fetch('assoc')['title']);
		$this->assertSame('French', $connection->execute("SELECT title FROM review_orphans_translations WHERE locale = 'fr'")->fetch('assoc')['title']);
	}

	public function testOrphanShadowDeleteChangesOnlySelectedLocale(): void {
		$this->post('/admin/translate/i18n-entries/delete/review_orphans_translations/1/de');
		$this->assertRedirect();
		$rows = ConnectionManager::get('test')->execute('SELECT locale FROM review_orphans_translations')->fetchAll('assoc');
		$this->assertSame([['locale' => 'fr']], $rows);
	}

	public function testBatchClassificationChangesOnlySelectedLocale(): void {
		$this->post('/admin/translate/i18n-entries/batch/review_orphans_translations', ['batch_action' => 'batchUpdateAuto', 'entry_ids' => [json_encode([1, 'de'])], 'auto' => 0]);
		$this->assertRedirect();
		$rows = ConnectionManager::get('test')->execute('SELECT locale, auto FROM review_orphans_translations ORDER BY locale')->fetchAll('assoc');
		$this->assertFalse((bool)$rows[0]['auto']);
		$this->assertTrue((bool)$rows[1]['auto']);
	}

	public function testCompositeBatchRejectsAmbiguousIds(): void {
		$this->post('/admin/translate/i18n-entries/batch-update-auto/review_orphans_translations', ['entry_ids' => [1], 'auto' => 0]);
		$this->assertResponseCode(404);
		$rows = ConnectionManager::get('test')->execute('SELECT auto FROM review_orphans_translations')->fetchAll('assoc');
		$this->assertTrue((bool)$rows[0]['auto']);
		$this->assertTrue((bool)$rows[1]['auto']);
	}

	public function testOrphanEntriesProvideCompositeSelection(): void {
		$this->get('/admin/translate/i18n-entries/entries/review_orphans_translations');
		$this->assertResponseOk();
		$this->assertResponseContains('value="[1,&quot;de&quot;]"');
		$this->assertResponseContains('/view/review_orphans_translations/1/de');
	}

	public function testSourceReferenceReadsUntranslatedBaseContent(): void {
		$table = TableRegistry::getTableLocator()->get('ReviewArticles', ['table' => 'review_articles']);
		$table->addBehavior('Translate', ['fields' => ['title'], 'strategyClass' => ShadowTableStrategy::class, 'translationTable' => 'ReviewArticlesTranslations']);
		$table->getBehavior('Translate')->setLocale('de');
		$this->assertSame('Manual title', $table->get(1)->get('title'));
		ConnectionManager::get('test')->insert('review_articles_i18n', ['id' => 1, 'locale' => 'de', 'model' => 'ReviewArticles', 'foreign_key' => 1, 'field' => 'title', 'content' => 'Current', 'auto' => 1]);
		$this->get('/admin/translate/i18n-entries/edit/review_articles_i18n/1');
		$this->assertResponseOk();
		$this->assertSame('Title', $this->viewVariable('sourceText'));
	}

	public function testZeroSourceAndTranslationAreKept(): void {
		$connection = ConnectionManager::get('test');
		$connection->execute("UPDATE review_articles SET title = '0', content = '0'");
		$connection->execute('DELETE FROM review_articles_translations');
		$this->post('/admin/translate/i18n-entries/auto-translate-record/review_articles_translations/1', ['locales' => ['de']]);
		$this->assertRedirect();
		$row = $connection->execute('SELECT * FROM review_articles_translations')->fetch('assoc');
		$this->assertSame('0', $row['title']);
		$this->assertSame('0', $row['content']);
	}

	public function testBaseFieldStatusIncludesZero(): void {
		ConnectionManager::get('test')->execute("UPDATE review_articles SET title = '0'");
		$this->get('/admin/translate/i18n-entries/entries/review_articles_translations');
		$this->assertResponseOk();
		$this->assertTrue($this->viewVariable('baseFieldStatus')[1]['title']);
	}

	protected function useFormProtection(bool $token = true): void {
		Configure::write('debug', false);
		Security::setSalt('translation-entry-test-salt');
		EventManager::instance()->on('Controller.initialize', function (EventInterface $event): void {
			$event->getSubject()->loadComponent('FormProtection');
		});
		if ($token) {
			$this->setUnlockedFields(['entry_ids']);
			$this->enableSecurityToken();
		}
	}

	public function testProtectedBatchAllowsDynamicSelections(): void {
		$this->useFormProtection(false);
		$token = (new FormProtector(['unlockedFields' => ['batch_action', 'auto']]))->buildTokenData('/admin/translate/i18n-entries/batch/review_orphans_translations', 'cli');
		unset($token['debug']);
		$this->post('/admin/translate/i18n-entries/batch/review_orphans_translations', ['batch_action' => 'batchUpdateAuto', 'entry_ids' => [json_encode([1, 'de'])], 'auto' => 0, '_Token' => $token]);
		$this->assertRedirect();
	}

	public function testProtectedBatchRejectsMissingToken(): void {
		$this->useFormProtection(false);
		$this->disableErrorHandlerMiddleware();
		$this->expectException(FormProtectionException::class);
		$this->post('/admin/translate/i18n-entries/batch/review_orphans_translations', ['batch_action' => 'batchUpdateAuto', 'entry_ids' => [json_encode([1, 'de'])], 'auto' => 0]);
	}

	public function testZeroEavTranslationIsKept(): void {
		$connection = ConnectionManager::get('test');
		$connection->execute("UPDATE review_articles SET title = '0'");
		$connection->insert('review_articles_i18n', ['id' => 1, 'locale' => 'de', 'model' => 'ReviewArticles', 'foreign_key' => 1, 'field' => 'title', 'content' => null, 'auto' => 1]);
		$this->post('/admin/translate/i18n-entries/auto-translate/review_articles_i18n', ['entry_ids' => [1], 'source_locale' => 'en']);
		$this->assertRedirect();
		$row = $connection->execute('SELECT content FROM review_articles_i18n')->fetch('assoc');
		$this->assertSame('0', $row['content']);
	}

	public function testProtectedRecordBatchAllowsDynamicSelections(): void {
		$this->useFormProtection(false);
		$token = (new FormProtector())->buildTokenData('/admin/translate/i18n-entries/auto-translate-batch/review_articles_translations', 'cli');
		unset($token['debug']);
		$this->post('/admin/translate/i18n-entries/auto-translate-batch/review_articles_translations', ['record_ids' => [1], 'locales' => ['de'], '_Token' => $token]);
		$this->assertRedirect();
		$row = ConnectionManager::get('test')->execute('SELECT content FROM review_articles_translations')->fetch('assoc');
		$this->assertSame('tnetnoC', $row['content']);
	}

	public function testUuidSourceReferenceIsKept(): void {
		$connection = ConnectionManager::get('test');
		$connection->execute('DROP TABLE review_articles');
		$connection->execute('CREATE TABLE review_articles (id VARCHAR(36) PRIMARY KEY, title VARCHAR(255))');
		$connection->execute('DROP TABLE review_articles_i18n');
		$connection->execute('CREATE TABLE review_articles_i18n (id INTEGER PRIMARY KEY, locale VARCHAR(6), model VARCHAR(100), foreign_key VARCHAR(36), field VARCHAR(100), content TEXT, auto BOOLEAN)');
		$id = 'b56372b1-dcae-4d01-8010-9a2d124426db';
		$connection->insert('review_articles', ['id' => $id, 'title' => 'Uuid source']);
		$connection->insert('review_articles_i18n', ['id' => 1, 'locale' => 'de', 'model' => 'ReviewArticles', 'foreign_key' => $id, 'field' => 'title', 'content' => 'Current', 'auto' => 1]);
		$this->get('/admin/translate/i18n-entries/edit/review_articles_i18n/1');
		$this->assertResponseOk();
		$this->assertSame('Uuid source', $this->viewVariable('sourceText'));
	}

}
