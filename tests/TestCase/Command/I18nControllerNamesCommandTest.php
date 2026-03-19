<?php
declare(strict_types=1);

namespace Translate\Test\TestCase\Command;

use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * @uses \Translate\Command\I18nControllerNamesCommand
 */
class I18nControllerNamesCommandTest extends TestCase {

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
		$this->exec('i18n controller_names --help');
		$this->assertExitSuccess();
		$this->assertOutputContains('List controller names in singular and plural');
		$this->assertOutputContains('--plugin');
		$this->assertOutputContains('--app-only');
	}

	/**
	 * @return void
	 */
	public function testExecuteAppOnly(): void {
		$this->exec('i18n controller_names --app-only');
		$this->assertExitSuccess();
		// Test app has no controllers (AppController is skipped)
		$this->assertErrorContains('No controllers found');
	}

	/**
	 * @return void
	 */
	public function testExecuteWithPlugin(): void {
		$this->exec('i18n controller_names --plugin=Translate');
		$this->assertExitSuccess();
		$this->assertOutputContains('Translate');
		$this->assertOutputContains('Controller');
		$this->assertOutputContains('Singular');
		$this->assertOutputContains('Plural');
		$this->assertOutputContains('Total:');
	}

	/**
	 * @return void
	 */
	public function testExecuteAll(): void {
		$this->exec('i18n controller_names');
		$this->assertExitSuccess();
		// Test app only loads Tools plugin, which may or may not have controllers
		// Just verify the command runs successfully
	}

}
