<?php
namespace Translate\Test\TestCase\Shell;

use Cake\Console\ConsoleIo;
use Cake\Core\Plugin;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Tools\TestSuite\ConsoleOutput;
use Translate\Shell\Task\ExtractTask;

class ExtractTaskTest extends TestCase {

	/**
	 * @var array
	 */
	public $fixtures = [
		'plugin.translate.translate_strings',
		'plugin.translate.translate_domains',
		'plugin.translate.translate_projects',
	];

	/**
	 * @var \Translate\Shell\Task\ExtractTask|\PHPUnit_Framework_MockObject_MockObject
	 */
	public $Task;

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

		$this->Task = $this->getMockBuilder(ExtractTask::class)
			->setMethods(['_stop'])
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
		unset($this->Task);
	}

	/**
	 * @return void
	 */
	public function testExtract() {
		$this->Task->setProjectId(1);
		$this->Task->setPath(Plugin::path('Translate') . 'tests' . DS . 'test_files' . DS . 'src' . DS);

		/** @var \Translate\Model\Table\TranslateStringsTable $TranslateStrings */
		$TranslateStrings = TableRegistry::getTableLocator()->get('Translate.TranslateStrings');
		$TranslateStrings->truncate();

		$this->Task->main();

		$translateStrings = $TranslateStrings->find()->orderDesc('id')->all()->toArray();

		$this->assertTextContains('foobar', $translateStrings[0]->name);
		$this->assertTextContains('Controller/FooController.php:12', $translateStrings[1]->references);
		$this->assertNotSame($translateStrings[0]->translate_domain_id, $translateStrings[1]->translate_domain_id);
	}

}
