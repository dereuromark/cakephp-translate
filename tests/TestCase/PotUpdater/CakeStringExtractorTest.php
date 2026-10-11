<?php

namespace Translate\Test\TestCase\PotUpdater;

use Cake\Console\ConsoleIo;
use Cake\Console\TestSuite\StubConsoleOutput;
use Cake\TestSuite\TestCase;
use Cake\Utility\Filesystem;
use CakeI18nLint\Command\SyncCommand;
use CakeI18nLint\Po\Parser;
use Translate\PotUpdater\CakeStringExtractor;
use Translate\PotUpdater\PotParser;
use Translate\PotUpdater\PotUpdater;

class CakeStringExtractorTest extends TestCase {

	public function testSymlinkedProjectRootUsesRelativeReferences(): void {
		$root = TMP . 'translate-pot-' . uniqid();
		$link = $root . '-link';
		mkdir($root . '/src', 0770, true);
		file_put_contents($root . '/src/Messages.php', "<?php\n__d('messages', 'Hello');\n");
		try {
			if (!symlink($root, $link)) {
				$this->markTestSkipped('Symbolic links are unavailable');
			}
			$strings = (new CakeStringExtractor())->extractStrings([$link . '/src'], 'messages', $link);
			$this->assertSame(['./src/Messages.php:2'], $strings['messages']['Hello']['references']);
		} finally {
			if (is_link($link)) {
				unlink($link);
			}
			(new Filesystem())->deleteDir($root);
		}
	}

	public function testExtractionPotUpdateAndPoSync(): void {
		$root = TMP . 'translate-pot-' . uniqid();
		mkdir($root . '/src', 0770, true);
		mkdir($root . '/resources/locales/de', 0770, true);
		file_put_contents($root . '/composer.json', '{"name":"test/messages"}');
		file_put_contents($root . '/src/Messages.php', <<<'SOURCE'
<?php
__d('messages', 'Hello');
__dxn('messages', 'count', 'One item', '{0} items', 2);
__dx('messages', 'button', 'Open');
__dxn('messages', 'time', 'One day', '{0} days', 2);
SOURCE);
		try {
			$extractor = new CakeStringExtractor();
			$strings = $extractor->extractStringsForDomain([$root . '/src'], 'messages');
			$this->assertArrayHasKey('Hello', $strings);
			$this->assertSame('{0} items', $strings["count\x04One item"]['msgid_plural']);
			$this->assertSame('count', $strings["count\x04One item"]['msgctxt']);
			$this->assertSame(['./src/Messages.php:2'], $strings['Hello']['references']);
			$this->assertSame($strings, $extractor->extractStringsForDomain([$root . '/src'], 'messages'));

			ob_start();
			try {
				$code = (new PotUpdater($root))->run(['--update', '--quiet', '--domain=messages']);
			} finally {
				ob_end_clean();
			}
			$this->assertSame(0, $code);
			$pot = (new PotParser())->parse($root . '/resources/locales/messages.pot');
			$this->assertSame('{0} items', $pot["count\x04One item"]['msgid_plural']);
			$this->assertSame(['./src/Messages.php:2'], $pot['Hello']['references']);

			$poFile = $root . '/resources/locales/de/messages.po';
			file_put_contents($poFile, <<<'PO'
msgid ""
msgstr "Language: de\nPlural-Forms: nplurals=2; plural=(n != 1);\n"

msgid "Hello"
msgstr "Hallo"

# Translator note
msgctxt "button"
msgid "Open"
msgstr "Öffnen"

msgctxt "count"
msgid "One item"
msgid_plural "{0} items"
msgstr[0] "Ein Eintrag"
msgstr[1] "{0} Einträge"

msgid "Removed"
msgstr "Entfernt"
PO);
			$output = new StubConsoleOutput();
			$code = (new SyncCommand())->run(['--root', $root], new ConsoleIo($output, $output));
			$this->assertSame(0, $code, $output->output());
			$messages = (new Parser())->parseFile($poFile)->messages();
			$this->assertSame(['Hallo'], $messages["\x04Hello"]->msgstr);
			$this->assertSame('{0} items', $messages["count\x04One item"]->msgidPlural);
			$this->assertSame(['Ein Eintrag', '{0} Einträge'], $messages["count\x04One item"]->msgstr);
			$this->assertSame(['Öffnen'], $messages["button\x04Open"]->msgstr);
			$this->assertSame('{0} days', $messages["time\x04One day"]->msgidPlural);
			$this->assertArrayNotHasKey("\x04Removed", $messages);
			$firstPot = file_get_contents($root . '/resources/locales/messages.pot');
			$firstPo = file_get_contents($poFile);
			$this->assertStringContainsString('# Translator note', $firstPo);
			ob_start();
			try {
				$code = (new PotUpdater($root))->run(['--update', '--quiet', '--domain=messages']);
			} finally {
				ob_end_clean();
			}
			$this->assertSame(0, $code);
			$this->assertSame(0, (new SyncCommand())->run(['--root', $root], new ConsoleIo($output, $output)));
			$this->assertSame($firstPot, file_get_contents($root . '/resources/locales/messages.pot'));
			$this->assertSame($firstPo, file_get_contents($poFile));
		} finally {
			(new Filesystem())->deleteDir($root);
		}
	}

}
