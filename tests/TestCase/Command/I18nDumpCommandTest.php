<?php

namespace Translate\Test\TestCase\Command;

use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * @uses \Translate\Command\I18nDumpCommand
 */
class I18nDumpCommandTest extends TestCase {

	use ConsoleIntegrationTestTrait;

	/**
	 * @var array<string>
	 */
	protected array $fixtures = [
		'plugin.Translate.TranslateProjects',
		'plugin.Translate.TranslateDomains',
		'plugin.Translate.TranslateLocales',
		'plugin.Translate.TranslateStrings',
		'plugin.Translate.TranslateTerms',
	];

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
	 * @return void
	 */
	public function testHelp(): void {
		$this->exec('i18n dump_from_db --help');
		$this->assertExitSuccess();
		$this->assertOutputContains('Dump translations from database to PO files');
		$this->assertOutputContains('--paths');
		$this->assertOutputContains('--plugin');
	}

	/**
	 * @return void
	 */
	public function testExecuteWithFixtures(): void {
		$outputPath = TMP . 'test_dump' . DS;
		if (!is_dir($outputPath)) {
			mkdir($outputPath, 0755, true);
		}

		$this->exec('i18n dump_from_db --paths=' . $outputPath);
		$this->assertExitSuccess();
		$this->assertOutputContains('Done:');
	}

	/**
	 * @return void
	 */
	public function testExecuteNoActiveDomains(): void {
		// Deactivate all domains to trigger abort
		$this->fetchTable('Translate.TranslateDomains')->updateAll(['active' => 0], []);

		$this->exec('i18n dump_from_db');
		$this->assertExitError();
		$this->assertErrorContains('No active domains found');
	}

}
