<?php

namespace Translate\Test\TestCase\I18n;

use Cake\Core\Configure;
use Cake\I18n\I18n;
use Cake\TestSuite\TestCase;
use Translate\Filesystem\Folder;
use Translate\I18n\DbMessagesLoader;
use Translate\Model\Entity\TranslateProject;

class DbMessagesLoaderTest extends TestCase {

	/**
	 * @var array<string>
	 */
	protected array $fixtures = [
		'plugin.Translate.TranslateTerms',
		'plugin.Translate.TranslateStrings',
		'plugin.Translate.TranslateDomains',
		'plugin.Translate.TranslateLocales',
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

		$this->skipIf(!version_compare(Configure::version(), '5.2.4', '>='), 'Only for CakePHP 5.2.4+');

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
	 * Regression: multi-project setups can target a non-default project by
	 * passing the project id to the constructor. Before this change the
	 * loader hard-coded "default TYPE_APP project" via internal lookup, so
	 * translations on non-default projects were unreachable.
	 *
	 * @return void
	 */
	public function testHonorsExplicitProjectId() {
		// I18n caches materialized translators across config() reconfigurations
		// unless the cache is explicitly cleared between tests.
		I18n::clear();

		$projects = $this->fetchTable('Translate.TranslateProjects');
		$secondProject = $projects->newEntity([
			'name' => 'Second',
			'type' => TranslateProject::TYPE_APP,
			'default' => false,
			'active' => true,
		]);
		$projects->saveOrFail($secondProject);

		/** @var \Translate\Model\Table\TranslateStringsTable $TranslateStrings */
		$TranslateStrings = $this->fetchTable('Translate.TranslateStrings');
		$secondDomain = $TranslateStrings->TranslateDomains->newEntity([
			'translate_project_id' => $secondProject->id,
			'name' => 'default',
			'active' => true,
		]);
		$TranslateStrings->TranslateDomains->saveOrFail($secondDomain);

		$secondLocale = $TranslateStrings->TranslateTerms->TranslateLocales->init(
			'DE',
			'de',
			'de',
			(int)$secondProject->id,
		);

		$secondString = $TranslateStrings->import(
			['name' => 'Sing', 'content' => 'SecondProjectTrans'],
			$secondDomain->id,
		);
		$TranslateStrings->TranslateTerms->import(
			['name' => 'Sing', 'content' => 'SecondProjectTrans'],
			$secondString->id,
			$secondLocale->id,
		);

		$secondProjectId = (int)$secondProject->id;

		// Sanity check the fixture/setup actually produced a second-project term.
		$terms = $this->fetchTable('Translate.TranslateTerms');
		$secondTerm = $terms->find()
			->contain(['TranslateStrings' => 'TranslateDomains', 'TranslateLocales'])
			->where(['TranslateLocales.translate_project_id' => $secondProjectId])
			->first();
		$this->assertNotNull($secondTerm, 'setup precondition: second-project term must exist');
		$this->assertSame('SecondProjectTrans', $secondTerm->content);

		// Invoke the loader directly to confirm project-id filtering works.
		$loader = new DbMessagesLoader('default', 'de', null, 'default', $secondProjectId);
		$package = $loader();
		$messages = $package->getMessages();
		$this->assertArrayHasKey('Sing', $messages);
		$this->assertSame('SecondProjectTrans', $messages['Sing']['_context']['']);
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

		$de = $TranslateStrings->TranslateTerms->TranslateLocales->init('DE', 'de', 'de', 1);
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
