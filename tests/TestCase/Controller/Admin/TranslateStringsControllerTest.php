<?php

namespace Translate\Test\TestCase\Controller\Admin;

use App\Translator\Engine\Test;
use App\Translator\Engine\TestMore;
use Cake\Core\Configure;
use Cake\Filesystem\Folder;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestCase;

/**
 * Translate\Controller\Admin\TranslateStringsController Test Case
 *
 * @uses \Translate\Controller\Admin\TranslateStringsController
 */
class TranslateStringsControllerTest extends IntegrationTestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	protected $fixtures = [
		'plugin.Translate.TranslateStrings',
		'plugin.Translate.TranslateDomains',
		'plugin.Translate.TranslateLanguages',
		'plugin.Translate.TranslateProjects',
		'plugin.Translate.Users',
	];

	/**
	 * Test index method
	 *
	 * @return void
	 */
	public function testIndex() {
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
		$TranslateStrings = TableRegistry::getTableLocator()->get('Translate.TranslateStrings');
		$count = $TranslateStrings->find()->count();

		$folder = new Folder();
		$folder->copy(LOCALE, [
			'from' => ROOT . DS . 'tests' . DS . 'test_files' . DS . 'Locale' . DS,
		]);

		$data = [
			'sel_pot' => [
				'default',
			],
			'sel_po' => [
			],
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
		Configure::write('Translate.engine', [Test::class, TestMore::class]);

		$id = 1;
		$this->TranslateStrings = TableRegistry::getTableLocator()->get('Translate.TranslateStrings');
		$record = $this->TranslateStrings->get($id);

		$groupId = $record->translate_domain_id;
		$record = $this->TranslateStrings->TranslateDomains->get($groupId);

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
		$id = 1;
		$this->post(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateStrings', 'action' => 'delete', $id]);

		$this->assertResponseCode(302);
		$this->assertRedirect();
	}

}
