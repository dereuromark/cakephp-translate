<?php

namespace Translate\Test\TestCase\Command;

use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\Core\Plugin;
use Cake\TestSuite\TestCase;

/**
 * @uses \Translate\Command\I18nExtractCommand
 */
class I18nExtractCommandTest extends TestCase {

	use ConsoleIntegrationTestTrait;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		$this->setAppNamespace();
		$this->configApplication(
			'TestApp\Application',
			[TESTS . 'test_app' . DS . 'config' . DS],
		);
	}

	/**
	 * tearDown
	 *
	 * @return void
	 */
	public function tearDown(): void {
		parent::tearDown();
		unset($this->command);
	}

	/**
	 * @return void
	 */
	public function testExecute() {
		$this->exec(
			'i18n extract_to_db '
			. '--extract-core=no '
			. '--merge=no '
			. '--paths=' . Plugin::path('Translate') . 'tests' . DS . 'test_files' . DS . 'src' . DS,
		);
		$this->assertExitSuccess();

		$translateStrings = $this->fetchTable('Translate.TranslateStrings')->find()->orderByDesc('id')->all()->toArray();

		$this->assertTextContains('foobar', $translateStrings[0]->name);
		$this->assertTextContains('Controller/FooController.php:13', $translateStrings[1]->references);
		$this->assertNotSame($translateStrings[0]->translate_domain_id, $translateStrings[1]->translate_domain_id);
	}

}
