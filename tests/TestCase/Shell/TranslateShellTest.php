<?php

namespace Translate\Test\TestCase\Shell;

use Cake\Console\ConsoleIo;
use Cake\TestSuite\TestCase;
use Tools\TestSuite\ConsoleOutput;
use Translate\Shell\TranslateShell;

class TranslateShellTest extends TestCase {

	/**
	 * @var \Translate\Shell\TranslateShell|\PHPUnit\Framework\MockObject\MockObject
	 */
	public $Shell;

	/**
	 * @var \Tools\TestSuite\ConsoleOutput
	 */
	protected $out;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();

		$this->out = new ConsoleOutput();
		$this->err = new ConsoleOutput();
		$io = new ConsoleIo($this->out, $this->err);

		$this->Shell = $this->getMockBuilder(TranslateShell::class)
			->setMethods(['in', 'err', '_stop'])
			->setConstructorArgs([$io])
			->getMock();
	}

	/**
	 * tearDown
	 *
	 * @return void
	 */
	public function tearDown() {
		parent::tearDown();
		unset($this->Shell);
	}

	/**
	 * @return void
	 */
	public function testHelp() {
		$this->Shell->runCommand(['help']);
		$output = $this->out->output();

		$expected = 'Run `bin/cake i18n extract` first';
		$this->assertTextContains($expected, $output);
	}

}
