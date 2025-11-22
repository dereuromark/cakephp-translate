<?php

namespace Translate\Test\TestCase\PotUpdater;

use PHPUnit\Framework\TestCase;
use Translate\PotUpdater\PotParser;

/**
 * @uses \Translate\Utility\PotParser
 */
class PotParserTest extends TestCase {

	/**
	 * Test parsing basic POT content
	 *
	 * @return void
	 */
	public function testParseBasicContent(): void {
		$content = <<<'POT'
# LANGUAGE translation of CakePHP Application
#, fuzzy
msgid ""
msgstr ""
"Content-Type: text/plain; charset=UTF-8\n"

#: src/Controller/UsersController.php:15
msgid "Hello World"
msgstr ""

#: src/View/Helper/MyHelper.php:20
msgid "Another string"
msgstr ""
POT;

		$parser = new PotParser();
		$entries = $parser->parseContent($content);

		$this->assertCount(2, $entries);
		$this->assertArrayHasKey('Hello World', $entries);
		$this->assertArrayHasKey('Another string', $entries);

		$this->assertSame('Hello World', $entries['Hello World']['msgid']);
		$this->assertNull($entries['Hello World']['msgid_plural']);
		$this->assertNull($entries['Hello World']['msgctxt']);
		$this->assertSame(['src/Controller/UsersController.php:15'], $entries['Hello World']['references']);
	}

	/**
	 * Test parsing plural strings
	 *
	 * @return void
	 */
	public function testParsePluralStrings(): void {
		$content = <<<'POT'
#: src/Template/Posts/index.ctp:5
msgid "One post"
msgid_plural "{0} posts"
msgstr[0] ""
msgstr[1] ""
POT;

		$parser = new PotParser();
		$entries = $parser->parseContent($content);

		$this->assertCount(1, $entries);
		$this->assertSame('One post', $entries['One post']['msgid']);
		$this->assertSame('{0} posts', $entries['One post']['msgid_plural']);
	}

	/**
	 * Test parsing context strings
	 *
	 * @return void
	 */
	public function testParseContextStrings(): void {
		$content = <<<'POT'
#: src/Template/Calendar.ctp:10
msgctxt "month"
msgid "May"
msgstr ""

#: src/Template/Names.ctp:5
msgctxt "name"
msgid "May"
msgstr ""

#: src/Template/Other.ctp:1
msgid "May"
msgstr ""
POT;

		$parser = new PotParser();
		$entries = $parser->parseContent($content);

		$this->assertCount(3, $entries);
		$this->assertArrayHasKey("month\x04May", $entries);
		$this->assertArrayHasKey("name\x04May", $entries);
		$this->assertArrayHasKey('May', $entries);

		$this->assertSame('month', $entries["month\x04May"]['msgctxt']);
		$this->assertSame('name', $entries["name\x04May"]['msgctxt']);
	}

	/**
	 * Test parsing multiline strings
	 *
	 * @return void
	 */
	public function testParseMultilineStrings(): void {
		$content = <<<'POT'
#: src/Template/Email.ctp:15
msgid ""
"Line 1\n"
"Line 2\n"
"Line 3"
msgstr ""
POT;

		$parser = new PotParser();
		$entries = $parser->parseContent($content);

		$this->assertCount(1, $entries);
		$key = "Line 1\nLine 2\nLine 3";
		$this->assertArrayHasKey($key, $entries);
	}

	/**
	 * Test parsing escaped characters
	 *
	 * @return void
	 */
	public function testParseEscapedCharacters(): void {
		$content = <<<'POT'
#: src/file.php:1
msgid "String with \"quotes\" and \\backslash"
msgstr ""
POT;

		$parser = new PotParser();
		$entries = $parser->parseContent($content);

		$expected = 'String with "quotes" and \\backslash';
		$this->assertArrayHasKey($expected, $entries);
	}

	/**
	 * Test parsing multiple references
	 *
	 * @return void
	 */
	public function testParseMultipleReferences(): void {
		$content = <<<'POT'
#: src/file1.php:10
#: src/file2.php:20 src/file3.php:30
msgid "Shared string"
msgstr ""
POT;

		$parser = new PotParser();
		$entries = $parser->parseContent($content);

		$this->assertCount(1, $entries);
		$refs = $entries['Shared string']['references'];
		$this->assertCount(3, $refs);
		$this->assertContains('src/file1.php:10', $refs);
		$this->assertContains('src/file2.php:20', $refs);
		$this->assertContains('src/file3.php:30', $refs);
	}

	/**
	 * Test parsing comments
	 *
	 * @return void
	 */
	public function testParseComments(): void {
		$content = <<<'POT'
#. This is a translator comment
#: src/file.php:1
msgid "With comment"
msgstr ""
POT;

		$parser = new PotParser();
		$entries = $parser->parseContent($content);

		$this->assertCount(1, $entries);
		$this->assertSame(['This is a translator comment'], $entries['With comment']['comments']);
	}

	/**
	 * Test parsing non-existent file
	 *
	 * @return void
	 */
	public function testParseNonExistentFile(): void {
		$parser = new PotParser();
		$entries = $parser->parse('/non/existent/file.pot');

		$this->assertEmpty($entries);
	}

}
