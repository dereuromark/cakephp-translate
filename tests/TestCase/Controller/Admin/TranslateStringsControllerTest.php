<?php

namespace Translate\Test\TestCase\Controller\Admin;

use App\Translator\Engine\Test;
use App\Translator\Engine\TestMore;
use Cake\Core\Configure;
use Shim\Filesystem\Folder;
use Shim\TestSuite\IntegrationTestCase;

/**
 * Translate\Controller\Admin\TranslateStringsController Test Case
 *
 * @uses \Translate\Controller\Admin\TranslateStringsController
 */
class TranslateStringsControllerTest extends IntegrationTestCase {

	/**
	 * Fixtures
	 *
	 * @var array<string>
	 */
	protected array $fixtures = [
		'plugin.Translate.TranslateStrings',
		'plugin.Translate.TranslateDomains',
		'plugin.Translate.TranslateLanguages',
		'plugin.Translate.TranslateProjects',
		'plugin.Translate.TranslateTerms',
		'plugin.Translate.Users',
	];

	/**
	 * Test index method
	 *
	 * @return void
	 */
	public function testIndex() {
		$this->disableErrorHandlerMiddleware();

		$this->get(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateStrings', 'action' => 'index']);

		$this->assertResponseCode(200);
		$this->assertNoRedirect();
	}

	/**
	 * Test view method
	 *
	 * @return void
	 */
	public function testView() {
		$id = 1;
		$this->get(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateStrings', 'action' => 'view', $id]);

		$this->assertResponseCode(200);
		$this->assertNoRedirect();
	}

	/**
	 * Test view method
	 *
	 * @return void
	 */
	public function testExtract() {
		$this->disableErrorHandlerMiddleware();

		$this->get(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateStrings', 'action' => 'extract']);

		$this->assertResponseCode(200);
		$this->assertNoRedirect();
	}

	/**
	 * Test view method
	 *
	 * @return void
	 */
	public function testExtractPost() {
		$this->disableErrorHandlerMiddleware();

		$TranslateStrings = $this->fetchTable('Translate.TranslateStrings');
		$count = $TranslateStrings->find()->count();

		$folder = new Folder();
		$folder->copy(LOCALE, [
			'from' => ROOT . DS . 'tests' . DS . 'test_files' . DS . 'locales' . DS,
		]);

		$data = [
			'sel_pot' => [
				'default',
			],
			'sel_po' => [],
		];
		$this->post(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateStrings', 'action' => 'extract'], $data);

		$this->assertResponseCode(200);
		$this->assertNoRedirect();

		$countAfter = $TranslateStrings->find()->count();

		$this->assertSame(7, $countAfter - $count);

		$translateString = $TranslateStrings->find()->where(['name' => '{0} tree'])->firstOrFail();
		$this->assertSame('context', $translateString->context);
		$this->assertSame(['fuzzy', 'special'], $translateString->flags);

		$translateString = $TranslateStrings->find()->where(['name' => 'Your {0}.'])->firstOrFail();
		$this->assertSame('Template/Account/index.ctp:15;33
Template/Account/foo.ctp:15', $translateString->references);

		$translateString = $TranslateStrings->find()->where(['name' => 'untranslated-string'])->firstOrFail();
		$expected = '#. extracted-comments
#  translator-comments';
		$this->assertSame($expected, $translateString->comments);
	}

	/**
	 * Test extract with PO files auto-creates missing languages
	 *
	 * @return void
	 */
	public function testExtractPostWithPoFilesAutoCreatesLanguage() {
		$this->disableErrorHandlerMiddleware();

		$TranslateStrings = $this->fetchTable('Translate.TranslateStrings');
		$TranslateLanguages = $this->fetchTable('Translate.TranslateLanguages');
		$TranslateTerms = $this->fetchTable('Translate.TranslateTerms');

		// Verify "de" language doesn't exist
		$deLanguage = $TranslateLanguages->find()->where(['iso2' => 'de'])->first();
		$this->assertNull($deLanguage, 'German language should not exist yet');

		$folder = new Folder();
		$folder->copy(LOCALE, [
			'from' => ROOT . DS . 'tests' . DS . 'test_files' . DS . 'locales' . DS,
		]);

		$data = [
			'sel_pot' => [],
			'sel_po' => [
				'de-default',
			],
		];
		$this->post(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateStrings', 'action' => 'extract'], $data);

		$this->assertResponseCode(200);
		$this->assertNoRedirect();

		// Verify "de" language was auto-created
		$deLanguage = $TranslateLanguages->find()->where(['iso2' => 'de'])->first();
		$this->assertNotNull($deLanguage, 'German language should be auto-created');
		$this->assertSame('De', $deLanguage->name);
		$this->assertSame('de', $deLanguage->iso2);
		$this->assertSame('de', $deLanguage->locale);
		$this->assertTrue($deLanguage->active);

		// Verify translations were imported
		$termCount = $TranslateTerms->find()->where(['translate_language_id' => $deLanguage->id])->count();
		$this->assertGreaterThan(0, $termCount, 'Should have imported some German translations');
	}

	/**
	 * Test view method
	 *
	 * @return void
	 */
	public function testDump() {
		$this->get(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateStrings', 'action' => 'dump']);

		$this->assertResponseCode(200);
		$this->assertNoRedirect();
	}

	/**
	 * Test view method
	 *
	 * @return void
	 */
	public function testTranslate() {
		$this->disableErrorHandlerMiddleware();

		Configure::write('Translate.engine', [Test::class, TestMore::class]);

		$id = 1;
		$TranslateStrings = $this->fetchTable('Translate.TranslateStrings');
		$record = $TranslateStrings->get($id);

		$domainId = $record->translate_domain_id;
		$record = $TranslateStrings->TranslateDomains->get($domainId);

		$this->get(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateStrings', 'action' => 'translate', $id]);

		$this->assertResponseCode(200);
		$this->assertNoRedirect();
	}

	/**
	 * Test add method
	 *
	 * @return void
	 */
	public function testAdd() {
		$this->get(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateStrings', 'action' => 'add']);

		$this->assertResponseCode(200);
		$this->assertNoRedirect();
	}

	/**
	 * Test edit method
	 *
	 * @return void
	 */
	public function testEdit() {
		$this->disableErrorHandlerMiddleware();

		$id = 1;
		$this->get(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateStrings', 'action' => 'edit', $id]);

		$this->assertResponseCode(200);
		$this->assertNoRedirect();
	}

	/**
	 * Test delete method
	 *
	 * @return void
	 */
	public function testDelete() {
		$this->disableErrorHandlerMiddleware();

		$id = 1;
		$this->post(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateStrings', 'action' => 'delete', $id]);

		$this->assertResponseCode(302);
		$this->assertRedirect();
	}

}
