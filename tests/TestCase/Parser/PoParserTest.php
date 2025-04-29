<?php

namespace Translate\Test\TestCase\Parser;

use Cake\TestSuite\TestCase;
use Translate\Parser\PoParser;

class PoParserTest extends TestCase {

	/**
	 * @return void
	 */
	public function testDump() {
		$poParser = new PoParser();

		$poParser->setHeaders([
			'# Language translation',
		]);
		$poParser->setEntries([
			[
				'msgid' => 'foo',
				'msgstr' => 'fooo',
			],
		]);

		$poParser->write(TMP . 'file.po');
		$content = file_get_contents(TMP . 'file.po');
		$expected = <<<TXT
# Language translation

msgid "foo"
msgstr "fooo"
TXT;
		$this->assertEquals($expected, $content);
	}

}
