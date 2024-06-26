<?php

namespace Translate\Test\TestCase\Command;

use Cake\Console\ConsoleIo;
use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\TestSuite\TestCase;
use Shim\TestSuite\ConsoleOutput;
use Translate\Command\TranslationsCommand;

class TranslationsCommandTest extends TestCase {

	use ConsoleIntegrationTestTrait;

	/**
	 * @var \Translate\Command\TranslationsCommand|\PHPUnit\Framework\MockObject\MockObject
	 */
	public $command;

	/**
	 * @var \Shim\TestSuite\ConsoleOutput
	 */
	protected $out;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		$this->out = new ConsoleOutput();
		$this->err = new ConsoleOutput();
		$io = new ConsoleIo($this->out, $this->err);

		$this->command = $this->getMockBuilder(TranslationsCommand::class)
			->getMock();
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
	public function testHelp() {
		$this->command->executeCommand('help');
		$output = $this->out->output();

		//$expected = 'Run `bin/cake i18n extract` first';
		//$this->assertTextContains($expected, $output);
	}

}
