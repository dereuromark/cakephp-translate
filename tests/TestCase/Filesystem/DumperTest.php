<?php

namespace Translate\Test\TestCase\Filesystem;

use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use Translate\Filesystem\Dumper;
use Translate\Model\Entity\TranslateString;
use Translate\Model\Entity\TranslateTerm;

class DumperTest extends TestCase {

	/**
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();
		Configure::delete('Translate.noComments');
	}

	/**
	 * @return void
	 */
	public function tearDown(): void {
		parent::tearDown();
		Configure::delete('Translate.noComments');

		// Clean up test files
		$testFile = TMP . 'de' . DS . 'test.po';
		if (file_exists($testFile)) {
			unlink($testFile);
		}
		$testDir = TMP . 'de';
		if (is_dir($testDir)) {
			rmdir($testDir);
		}
	}

	/**
	 * Test basic dump with msgid and msgstr
	 *
	 * @return void
	 */
	public function testDumpBasic(): void {
		$dumper = new Dumper();

		$translateString = new TranslateString([
			'name' => 'Hello World',
			'plural' => null,
			'context' => null,
			'references' => null,
			'flags' => null,
			'comments' => null,
		]);

		$translation = new TranslateTerm([
			'content' => 'Hallo Welt',
			'translate_string' => $translateString,
		]);

		$result = $dumper->dump([$translation], 'test', 'de', TMP);

		$this->assertTrue($result);
		$this->assertFileExists(TMP . 'de' . DS . 'test.po');

		$content = file_get_contents(TMP . 'de' . DS . 'test.po');
		$this->assertStringContainsString('msgid "Hello World"', $content);
		$this->assertStringContainsString('msgstr "Hallo Welt"', $content);
	}

	/**
	 * Test dump with references
	 *
	 * @return void
	 */
	public function testDumpWithReferences(): void {
		$dumper = new Dumper();

		$translateString = new TranslateString([
			'name' => 'Test message',
			'plural' => null,
			'context' => null,
			'references' => "./src/Controller/TestController.php:42\n./src/Controller/TestController.php:99",
			'flags' => null,
			'comments' => null,
		]);

		$translation = new TranslateTerm([
			'content' => 'Test Nachricht',
			'translate_string' => $translateString,
		]);

		$result = $dumper->dump([$translation], 'test', 'de', TMP);

		$this->assertTrue($result);

		$content = file_get_contents(TMP . 'de' . DS . 'test.po');
		// Each reference should be on its own line
		$this->assertStringContainsString('#: ./src/Controller/TestController.php:42', $content);
		$this->assertStringContainsString('#: ./src/Controller/TestController.php:99', $content);
		$this->assertStringContainsString('msgid "Test message"', $content);
	}

	/**
	 * Test dump with flags
	 *
	 * @return void
	 */
	public function testDumpWithFlags(): void {
		$dumper = new Dumper();

		$translateString = new TranslateString([
			'name' => 'Fuzzy message',
			'plural' => null,
			'context' => null,
			'references' => null,
			'flags' => ['fuzzy', 'php-format'],
			'comments' => null,
		]);

		$translation = new TranslateTerm([
			'content' => 'Unscharfe Nachricht',
			'translate_string' => $translateString,
		]);

		$result = $dumper->dump([$translation], 'test', 'de', TMP);

		$this->assertTrue($result);

		$content = file_get_contents(TMP . 'de' . DS . 'test.po');
		$this->assertStringContainsString('#, fuzzy, php-format', $content);
	}

	/**
	 * Test dump with context
	 *
	 * @return void
	 */
	public function testDumpWithContext(): void {
		$dumper = new Dumper();

		$translateString = new TranslateString([
			'name' => 'Open',
			'plural' => null,
			'context' => 'verb',
			'references' => null,
			'flags' => null,
			'comments' => null,
		]);

		$translation = new TranslateTerm([
			'content' => 'Öffnen',
			'translate_string' => $translateString,
		]);

		$result = $dumper->dump([$translation], 'test', 'de', TMP);

		$this->assertTrue($result);

		$content = file_get_contents(TMP . 'de' . DS . 'test.po');
		$this->assertStringContainsString('msgctxt "verb"', $content);
		$this->assertStringContainsString('msgid "Open"', $content);
	}

	/**
	 * Test dump with comments
	 *
	 * @return void
	 */
	public function testDumpWithComments(): void {
		$dumper = new Dumper();

		$translateString = new TranslateString([
			'name' => 'Commented message',
			'plural' => null,
			'context' => null,
			'references' => null,
			'flags' => null,
			'comments' => 'This is a translator comment',
		]);

		$translation = new TranslateTerm([
			'content' => 'Kommentierte Nachricht',
			'translate_string' => $translateString,
		]);

		$result = $dumper->dump([$translation], 'test', 'de', TMP);

		$this->assertTrue($result);

		$content = file_get_contents(TMP . 'de' . DS . 'test.po');
		$this->assertStringContainsString('# This is a translator comment', $content);
	}

	/**
	 * Test dump with noComments config suppresses references, flags, and comments
	 *
	 * @return void
	 */
	public function testDumpWithNoCommentsConfig(): void {
		Configure::write('Translate.noComments', true);

		$dumper = new Dumper();

		$translateString = new TranslateString([
			'name' => 'No metadata message',
			'plural' => null,
			'context' => null,
			'references' => './src/Controller/TestController.php:42',
			'flags' => ['fuzzy'],
			'comments' => 'Should not appear',
		]);

		$translation = new TranslateTerm([
			'content' => 'Keine Metadaten Nachricht',
			'translate_string' => $translateString,
		]);

		$result = $dumper->dump([$translation], 'test', 'de', TMP);

		$this->assertTrue($result);

		$content = file_get_contents(TMP . 'de' . DS . 'test.po');
		// With noComments, references, flags, and comments should NOT appear
		$this->assertStringNotContainsString('#:', $content);
		$this->assertStringNotContainsString('#,', $content);
		$this->assertStringNotContainsString('# Should not appear', $content);
		// But the translation itself should still be there
		$this->assertStringContainsString('msgid "No metadata message"', $content);
		$this->assertStringContainsString('msgstr "Keine Metadaten Nachricht"', $content);
	}

	/**
	 * Test dump with plurals
	 *
	 * @return void
	 */
	public function testDumpWithPlurals(): void {
		$dumper = new Dumper();

		$translateString = new TranslateString([
			'name' => '{0} item',
			'plural' => '{0} items',
			'context' => null,
			'references' => null,
			'flags' => null,
			'comments' => null,
		]);

		$translation = new TranslateTerm([
			'content' => '{0} Artikel',
			'plural_2' => '{0} Artikel',
			'translate_string' => $translateString,
		]);

		$result = $dumper->dump([$translation], 'test', 'de', TMP);

		$this->assertTrue($result);

		$content = file_get_contents(TMP . 'de' . DS . 'test.po');
		$this->assertStringContainsString('msgid "{0} item"', $content);
		$this->assertStringContainsString('msgid_plural "{0} items"', $content);
		$this->assertStringContainsString('msgstr[0]', $content);
		$this->assertStringContainsString('msgstr[1]', $content);
	}

}
