<?php

namespace Translate\Test\TestCase\Command;

use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\Core\Plugin;
use Cake\TestSuite\TestCase;

/**
 * @uses \Translate\Command\I18nValidateCommand
 */
class I18nValidateCommandTest extends TestCase {

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
	 * @return void
	 */
	public function testHelp(): void {
		$this->exec('i18n validate --help');
		$this->assertExitSuccess();
		$this->assertOutputContains('Validate PO/POT translation files');
		$this->assertOutputContains('--key-based');
		$this->assertOutputContains('--json');
	}

	/**
	 * @return void
	 */
	public function testExecuteWithPath(): void {
		$path = Plugin::path('Translate') . 'tests' . DS . 'test_files' . DS . 'locales' . DS;

		$this->exec('i18n validate --paths=' . $path);
		$this->assertExitSuccess();
		$this->assertOutputContains('Files scanned:');
		$this->assertOutputContains('Summary');
	}

	/**
	 * @return void
	 */
	public function testExecuteWithSummaryOnly(): void {
		$path = Plugin::path('Translate') . 'tests' . DS . 'test_files' . DS . 'locales' . DS;

		$this->exec('i18n validate --paths=' . $path . ' --summary');
		$this->assertExitSuccess();
		$this->assertOutputContains('Summary');
		$this->assertOutputContains('Files scanned:');
	}

	/**
	 * @return void
	 */
	public function testExecuteWithJsonOutput(): void {
		$path = Plugin::path('Translate') . 'tests' . DS . 'test_files' . DS . 'locales' . DS;

		$this->exec('i18n validate --paths=' . $path . ' --json');
		$this->assertExitSuccess();

		$output = $this->_out->output();
		$decoded = json_decode($output, true);
		$this->assertIsArray($decoded);
		$this->assertArrayHasKey('summary', $decoded);
		$this->assertArrayHasKey('total_files', $decoded['summary']);
	}

	/**
	 * @return void
	 */
	public function testExecuteWithKeyBased(): void {
		$path = Plugin::path('Translate') . 'tests' . DS . 'test_files' . DS . 'locales' . DS;

		$this->exec('i18n validate --paths=' . $path . ' --key-based');
		$this->assertExitSuccess();
		$this->assertOutputContains('Summary');
	}

	/**
	 * @return void
	 */
	public function testExecuteWithNonExistentPath(): void {
		$this->exec('i18n validate --paths=/nonexistent/path');
		$this->assertExitSuccess();
		$this->assertErrorContains('Path not found');
	}

}
