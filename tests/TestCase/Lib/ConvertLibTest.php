<?php

namespace Translate\Test\TestCase\Lib;

use Cake\TestSuite\TestCase;
use Translate\Lib\ConvertLib;

class ConvertLibTest extends TestCase {

	/**
	 * @var \Translate\Lib\ConvertLib
	 */
	public $ConvertLib;

	/**
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$this->ConvertLib = new ConvertLib();
	}

	/**
	 * @return void
	 */
	public function testConvert() {
		$text = <<<TXT
Some <awesome>
"text".
TXT;

		$is = $this->ConvertLib->convert($text);
		$expected = 'Some &lt;awesome&gt;\n&quot;text&quot;.';
		$this->assertSame($expected, $is);
	}

	/**
	 * @return void
	 */
	public function testConvertHtml() {
		$text = <<<TXT
Some <b>awesome</b>
text.
TXT;

		$is = $this->ConvertLib->convert($text, ['escape' => false]);
		$expected = 'Some <b>awesome</b>\ntext.';
		$this->assertSame($expected, $is);
	}

	/**
	 * @return void
	 */
	public function testConvertParagraph() {
		$text = <<<TXT
Some awesome text.

Yes, really.
TXT;

		$is = $this->ConvertLib->convert($text, ['newline' => true]);
		$expected = <<<HTML
<p>Some awesome text.</p>
<p>Yes, really.</p>

HTML;
		$this->assertSame($expected, $is);
	}

}
