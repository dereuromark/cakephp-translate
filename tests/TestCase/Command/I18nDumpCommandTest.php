<?php

namespace Translate\Test\TestCase\Command;

use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\TestSuite\TestCase;

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

	public function testDumpWritesEveryLocaleToEveryOutputDirectory(): void {
		$locales = $this->fetchTable('Translate.TranslateLocales');
		$locale = $locales->saveOrFail($locales->newEntity([
			'translate_project_id' => 1,
			'name' => 'German',
			'iso2' => 'de',
			'locale' => 'de_DE',
			'active' => true,
		]));
		$terms = $this->fetchTable('Translate.TranslateTerms');
		$terms->saveOrFail($terms->newEntity([
			'translate_string_id' => 1,
			'translate_locale_id' => $locale->id,
			'content' => 'Willkommen',
		]));
		$paths = [TMP . 'dump_first', TMP . 'dump_second'];
		$this->exec('i18n dump_from_db --paths=' . implode(',', $paths));
		$this->assertExitSuccess();
		foreach ($paths as $path) {
			$this->assertFileExists($path . '/de_DE/Lorem ipsum dolor sit amet.po');
			$this->assertFileExists($path . '/en_US/Lorem ipsum dolor sit amet.po');
		}
		$this->assertOutputContains('Done: 4 files');
	}

}
