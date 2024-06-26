<?php

namespace Translate\Test\TestCase\Translator\Engine;

use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use Translate\Translator\Engine\Transltr;

class TransltrTest extends TestCase {

	/**
	 * @var \Translate\Translator\Engine\Transltr
	 */
	protected $Transltr;

	/**
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		$this->skipIf(!Configure::read('Transltr.live'), 'Requires confirmation for API live call');

		$this->Transltr = new Transltr();
	}

	/**
	 * @return void
	 */
	public function testTranslate() {
		$text = 'Father';
		$is = $this->Transltr->translate($text, 'de', 'en');

		$expected = 'Vater';
		$this->assertSame($expected, $is);
	}

}
