<?php

namespace Translate\Test\TestCase\Controller\Admin;

use Cake\Controller\Exception\FormProtectionException;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Datasource\ConnectionManager;
use Cake\Event\EventInterface;
use Cake\Event\EventManager;
use Cake\Form\FormProtector;
use Cake\Http\ServerRequest;
use Cake\ORM\Behavior\Translate\ShadowTableStrategy;
use Cake\ORM\TableRegistry;
use Cake\Utility\Security;
use Cake\View\Helper\FormHelper;
use Cake\View\View;
use Closure;
use DOMDocument;
use ReflectionProperty;
use TestApp\Translator\Engine\Test;
use Tools\ToolsPlugin;
use Translate\Test\TestCase\IntegrationTestCase;

class I18nEntriesControllerTest extends IntegrationTestCase {

	protected array $fixtures = [
		'plugin.Translate.TranslateProjects',
		'plugin.Translate.TranslateApiTranslations',
	];

	/**
	 * @var array<\Closure>
	 */
	protected array $listeners = [];

	protected ?string $originalSalt;

	protected mixed $originalDebug;

	protected mixed $originalEngine;

	protected bool $toolsInitiallyLoaded;

	protected mixed $originalLoadTools;

	protected function listen(Closure $listener): void {
		$this->listeners[] = $listener;
		EventManager::instance()->on('Controller.initialize', $listener);
	}

	protected function setUp(): void {
		parent::setUp();
		$this->toolsInitiallyLoaded = Plugin::isLoaded('Tools');
		$this->originalLoadTools = Configure::read('TestApp.loadTools');
		$this->originalSalt = (new ReflectionProperty(Security::class, 'salt'))->getValue();
		$this->originalDebug = Configure::read('debug');
		$this->originalEngine = Configure::read('Translate.engine');
		Configure::write('Translate.engine', Test::class);
		$this->listen(function (): void {
			if (Configure::read('TestApp.loadTools', true) && !Plugin::isLoaded('Tools')) {
				Plugin::getCollection()->add(new ToolsPlugin());
			}
		});
		$connection = ConnectionManager::get('test');
		$connection->execute('DROP TABLE IF EXISTS review_orphans_translations');
		$connection->execute('DROP TABLE IF EXISTS review_articles_i18n');
		$connection->execute('DROP TABLE IF EXISTS review_articles_translations');
		$connection->execute('DROP TABLE IF EXISTS review_articles');
		$connection->execute('CREATE TABLE review_articles (id INTEGER PRIMARY KEY, title VARCHAR(255), content TEXT)');
		$connection->execute('CREATE TABLE review_articles_translations (id INTEGER NOT NULL, locale VARCHAR(6) NOT NULL, title VARCHAR(255), content TEXT, auto BOOLEAN NOT NULL DEFAULT FALSE, PRIMARY KEY (id, locale))');
		$connection->execute('CREATE TABLE review_orphans_translations (id INTEGER NOT NULL, locale VARCHAR(6) NOT NULL, title VARCHAR(255), auto BOOLEAN NOT NULL DEFAULT FALSE, PRIMARY KEY (id, locale))');
		$connection->execute('CREATE TABLE review_articles_i18n (id INTEGER PRIMARY KEY, locale VARCHAR(6), model VARCHAR(100), foreign_key INTEGER, field VARCHAR(100), content TEXT, auto BOOLEAN)');
		$connection->insert('review_orphans_translations', ['id' => 1, 'locale' => 'de', 'title' => 'German', 'auto' => 1], ['auto' => 'boolean']);
		$connection->insert('review_orphans_translations', ['id' => 1, 'locale' => 'fr', 'title' => 'French', 'auto' => 1], ['auto' => 'boolean']);
		$connection->insert('review_articles', ['id' => 1, 'title' => 'Title', 'content' => 'Content']);
		$connection->insert('review_articles_translations', ['id' => 1, 'locale' => 'de', 'title' => 'Manual title', 'content' => null, 'auto' => 0], ['auto' => 'boolean']);
	}

	protected function tearDown(): void {
		$connection = ConnectionManager::get('test');
		$connection->execute('DROP TABLE IF EXISTS review_orphans_translations');
		$connection->execute('DROP TABLE IF EXISTS review_articles_i18n');
		$connection->execute('DROP TABLE IF EXISTS review_articles_translations');
		$connection->execute('DROP TABLE IF EXISTS review_articles');
		Configure::write('Translate.engine', $this->originalEngine);
		Configure::write('debug', $this->originalDebug);
		(new ReflectionProperty(Security::class, 'salt'))->setValue(null, $this->originalSalt);
		foreach ($this->listeners as $listener) {
			EventManager::instance()->off('Controller.initialize', $listener);
		}
		if ($this->originalLoadTools === null) {
			Configure::delete('TestApp.loadTools');
		} else {
			Configure::write('TestApp.loadTools', $this->originalLoadTools);
		}
		if ($this->toolsInitiallyLoaded && !Plugin::isLoaded('Tools')) {
			Plugin::getCollection()->add(new ToolsPlugin());
		} elseif (!$this->toolsInitiallyLoaded) {
			Plugin::getCollection()->remove('Tools');
		}
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
		ConnectionManager::get('test')->insert('review_articles_i18n', ['id' => 1, 'locale' => 'de', 'model' => 'ReviewArticles', 'foreign_key' => 1, 'field' => 'title', 'content' => 'Current', 'auto' => 1], ['auto' => 'boolean']);
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
		$this->listen(function (EventInterface $event): void {
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
		$connection->insert('review_articles_i18n', ['id' => 1, 'locale' => 'de', 'model' => 'ReviewArticles', 'foreign_key' => 1, 'field' => 'title', 'content' => null, 'auto' => 1], ['auto' => 'boolean']);
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
		$connection->insert('review_articles_i18n', ['id' => 1, 'locale' => 'de', 'model' => 'ReviewArticles', 'foreign_key' => $id, 'field' => 'title', 'content' => 'Current', 'auto' => 1], ['auto' => 'boolean']);
		$this->get('/admin/translate/i18n-entries/edit/review_articles_i18n/1');
		$this->assertResponseOk();
		$this->assertSame('Uuid source', $this->viewVariable('sourceText'));
	}

	public function testProtectedViewUnlocksHiddenSelection(): void {
		$this->useFormProtection(false);
		$this->get('/admin/translate/i18n-entries/view/review_orphans_translations/1/de');
		$this->assertResponseOk();
		$this->assertResponseContains('name="_Token[unlocked]" value="entry_ids"');
	}

	public function testProtectedEditUnlocksHiddenSelection(): void {
		ConnectionManager::get('test')->insert('review_articles_i18n', ['id' => 1, 'locale' => 'de', 'model' => 'ReviewArticles', 'foreign_key' => 1, 'field' => 'title', 'content' => null, 'auto' => true], ['auto' => 'boolean']);
		$this->useFormProtection(false);
		$this->get('/admin/translate/i18n-entries/edit/review_articles_i18n/1');
		$this->assertResponseOk();
		$this->assertResponseContains('name="_Token[unlocked]" value="entry_ids"');
	}

	protected function hiddenFormData(string $url, array $data, bool $postButton = false): array {
		$request = (new ServerRequest())->withAttribute('formTokenData', ['unlockedFields' => ['entry_ids']]);
		$form = new FormHelper(new View($request));
		if ($postButton) {
			$html = $form->postButton('Update', $url, ['data' => $data]);
		} else {
			$html = $form->create(null, ['url' => $url]);
			$html .= $form->hidden('entry_ids[]', ['value' => $data['entry_ids'][0]]);
			$html .= $form->hidden('source_locale', ['value' => 'en']);
			$html .= $form->end();
		}
		$document = new DOMDocument();
		$document->loadHTML($html);
		$encoded = [];
		foreach ($document->getElementsByTagName('input') as $input) {
			$encoded[] = urlencode($input->getAttribute('name')) . '=' . urlencode($input->getAttribute('value'));
		}
		parse_str(implode('&', $encoded), $result);

		return $result;
	}

	public function testProtectedViewButtonSubmitsHiddenArray(): void {
		$this->useFormProtection(false);
		$url = '/admin/translate/i18n-entries/batch-update-auto/review_orphans_translations';
		$data = $this->hiddenFormData($url, ['entry_ids' => [json_encode([1, 'de'])], 'auto' => false], true);
		$this->post($url, $data);
		$this->assertRedirect();
		$rows = ConnectionManager::get('test')->execute('SELECT locale, auto FROM review_orphans_translations ORDER BY locale')->fetchAll('assoc');
		$this->assertFalse((bool)$rows[0]['auto']);
		$this->assertTrue((bool)$rows[1]['auto']);
	}

	public function testProtectedEditTranslationSubmitsHiddenArray(): void {
		ConnectionManager::get('test')->insert('review_articles_i18n', ['id' => 1, 'locale' => 'de', 'model' => 'ReviewArticles', 'foreign_key' => 1, 'field' => 'title', 'content' => null, 'auto' => true], ['auto' => 'boolean']);
		$this->useFormProtection(false);
		$url = '/admin/translate/i18n-entries/auto-translate/review_articles_i18n';
		$data = $this->hiddenFormData($url, ['entry_ids' => [1]]);
		$this->post($url, $data);
		$this->assertRedirect();
		$this->assertSame('eltiT', ConnectionManager::get('test')->execute('SELECT content FROM review_articles_i18n')->fetch('assoc')['content']);
	}

	public function testSinglePrimaryKeyShadowSelectionUsesScalar(): void {
		$connection = ConnectionManager::get('test');
		$connection->execute('DROP TABLE review_orphans_translations');
		$connection->execute('CREATE TABLE review_orphans_translations (id INTEGER PRIMARY KEY, locale VARCHAR(6), title VARCHAR(255), auto BOOLEAN)');
		$connection->insert('review_orphans_translations', ['id' => 1, 'locale' => 'de', 'title' => 'German', 'auto' => true], ['auto' => 'boolean']);
		$this->get('/admin/translate/i18n-entries/entries/review_orphans_translations');
		$this->assertResponseOk();
		$this->assertFalse($this->viewVariable('hasCompositeKey'));
		$this->assertResponseContains('value="1" class="form-check-input entry-checkbox"');
	}

	public function testCompositeEavSelectionIncludesLocale(): void {
		$connection = ConnectionManager::get('test');
		$connection->execute('DROP TABLE review_articles_i18n');
		$connection->execute('CREATE TABLE review_articles_i18n (id INTEGER NOT NULL, locale VARCHAR(6) NOT NULL, model VARCHAR(100), foreign_key INTEGER, field VARCHAR(100), content TEXT, auto BOOLEAN, PRIMARY KEY (id, locale))');
		$connection->insert('review_articles_i18n', ['id' => 1, 'locale' => 'de', 'model' => 'ReviewArticles', 'foreign_key' => 1, 'field' => 'title', 'content' => 'German', 'auto' => true], ['auto' => 'boolean']);
		$this->get('/admin/translate/i18n-entries/entries/review_articles_i18n');
		$this->assertResponseOk();
		$this->assertTrue($this->viewVariable('hasCompositeKey'));
		$this->assertResponseContains('value="[1,&quot;de&quot;]"');
		$this->assertResponseContains('/view/review_articles_i18n/1/de');
	}

	public function testOrphanEntriesRenderWithoutToolsLoaded(): void {
		Configure::write('TestApp.loadTools', false);
		Plugin::getCollection()->remove('Tools');
		$this->get('/admin/translate/i18n-entries/entries/review_orphans_translations');
		$this->assertResponseOk();
		$this->assertFalse(Plugin::isLoaded('Tools'));
		$this->assertResponseContains('pagination-info');
	}

	public function testBaseEntriesRenderWithoutToolsLoaded(): void {
		Configure::write('TestApp.loadTools', false);
		Plugin::getCollection()->remove('Tools');
		$this->get('/admin/translate/i18n-entries/entries/review_articles_translations');
		$this->assertResponseOk();
		$this->assertFalse(Plugin::isLoaded('Tools'));
		$this->assertResponseContains('pagination-info');
	}

	public function testOrphanEntriesUseToolsPaginationWhenLoaded(): void {
		$this->get('/admin/translate/i18n-entries/entries/review_orphans_translations');
		$this->assertResponseOk();
		$this->assertTrue(Plugin::isLoaded('Tools'));
		$this->assertResponseContains('paging-description');
	}

	public function testBaseEntriesUseToolsPaginationWhenLoaded(): void {
		$this->get('/admin/translate/i18n-entries/entries/review_articles_translations');
		$this->assertResponseOk();
		$this->assertTrue(Plugin::isLoaded('Tools'));
		$this->assertResponseContains('paging-description');
	}

}
