<?php

namespace Translate\Test\TestCase\I18n;

use Cake\I18n\I18n;
use Cake\TestSuite\TestCase;
use Shim\Filesystem\Folder;
use Translate\I18n\DbMessagesLoader;

class DbMessagesLoaderTest extends TestCase {

	/**
	 * @var array
	 */
	protected array $fixtures = [
		'plugin.Translate.TranslateTerms',
		'plugin.Translate.TranslateStrings',
		'plugin.Translate.TranslateDomains',
		'plugin.Translate.TranslateLanguages',
		'plugin.Translate.TranslateProjects',
	];

	/**
	 * @var \Translate\I18n\DbMessagesLoader
	 */
	protected $messagesLoader;

	/**
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		$folder = new Folder();
		$folder->copy(LOCALE, [
			'from' => ROOT . DS . 'tests' . DS . 'test_files' . DS . 'Locale' . DS,
		]);

		$this->_setUpData();
	}

	/**
	 * @return void
	 */
	public function tearDown(): void {
		parent::tearDown();

		I18n::clear();
	}

	/**
	 * @return void
	 */
	public function testDefault() {
		I18n::config('default', function ($domain, $locale) {
			return new DbMessagesLoader(
				$domain,
				$locale,
			);
		});
		I18n::setLocale('de');

		$translated = __('Sing');
		$this->assertSame('SingTrans', $translated);

		$translated = __n('Sing', 'Plur', 2);
		$this->assertSame('PlurTrans', $translated);

		$translated = __x('MyContext', 'Sing');
		$this->assertSame('MySingTrans', $translated);

		$translated = __xn('MyContext', 'Sing', 'Plur', 2);
		$this->assertSame('MyPlurTrans', $translated);
	}

	/**
	 * @return void
	 */
	public function testDomain() {
		/*
		I18n::setTranslator('dom', function () {
			return new MessagesDbLoader(
				'default',
				'de',
			);
		}, 'de');
		*/
		I18n::config('dom', function ($domain, $locale) {
			return new DbMessagesLoader(
				$domain,
				$locale,
			);
		});
		I18n::setLocale('de');

		$translated = __d('dom', 'Sing');
		$this->assertSame('SingTrans', $translated);

		$translated = __dn('dom', 'Sing', 'Plur', 2);
		$this->assertSame('PlurTrans', $translated);

		$translated = __dx('dom', 'MyContext', 'Sing');
		$this->assertSame('MySingTrans', $translated);

		$translated = __dxn('dom', 'MyContext', 'Sing', 'Plur', 2);
		$this->assertSame('MyPlurTrans', $translated);
	}

	/**
	 * @return void
	 */
	protected function _setUpData() {
		/** @var \Translate\Model\Table\TranslateStringsTable $TranslateStrings */
		$TranslateStrings = $this->fetchTable('Translate.TranslateStrings');

		$de = $TranslateStrings->TranslateTerms->TranslateLanguages->init('DE', 'de', 'de', 1);
		$default = $TranslateStrings->TranslateDomains->getDomain(1);

		$translation = [
			'name' => 'Sing',
			'plural' => 'Plur',
			'content' => 'SingTrans',
			'plural_2' => 'PlurTrans',
		];
		$translateString = $TranslateStrings->import($translation, $default->id);
		$TranslateStrings->TranslateTerms->import($translation, $translateString->id, $de->id);

		$translation = [
			'context' => 'MyContext',
			'name' => 'Sing',
			'plural' => 'Plur',
			'content' => 'MySingTrans',
			'plural_2' => 'MyPlurTrans',
		];
		$translateString = $TranslateStrings->import($translation, $default->id);
		$TranslateStrings->TranslateTerms->import($translation, $translateString->id, $de->id);

		$translateDomain = $TranslateStrings->TranslateDomains->newEntity([
			'translate_project_id' => 1,
			'name' => 'dom',
			'active' => true,
		]);
		$dom = $TranslateStrings->TranslateDomains->save($translateDomain, ['strict' => true]);

		$translation = [
			'name' => 'Sing',
			'plural' => 'Plur',
			'content' => 'SingTrans',
			'plural_2' => 'PlurTrans',
		];
		$translateString = $TranslateStrings->import($translation, $dom->id);
		$TranslateStrings->TranslateTerms->import($translation, $translateString->id, $de->id);

		$translation = [
			'context' => 'MyContext',
			'name' => 'Sing',
			'plural' => 'Plur',
			'content' => 'MySingTrans',
			'plural_2' => 'MyPlurTrans',
		];
		$translateString = $TranslateStrings->import($translation, $dom->id);
		$TranslateStrings->TranslateTerms->import($translation, $translateString->id, $de->id);
	}

}
