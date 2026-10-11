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

	public function testExtractionPotUpdateAndPoSync(): void {
		$root = TMP . 'translate-pot-' . uniqid();
		mkdir($root . '/src', 0770, true);
		mkdir($root . '/resources/locales/de', 0770, true);
		file_put_contents($root . '/composer.json', '{"name":"test/messages"}');
		file_put_contents($root . '/src/Messages.php', <<<'SOURCE'
<?php
__d('messages', 'Hello');
__dxn('messages', 'count', 'One item', '{0} items', 2);
SOURCE);
		try {
			$extractor = new CakeStringExtractor();
			$strings = $extractor->extractStringsForDomain([$root . '/src'], 'messages');
			$this->assertArrayHasKey('Hello', $strings);
			$this->assertSame('{0} items', $strings["count\x04One item"]['msgid_plural']);
			$this->assertSame('count', $strings["count\x04One item"]['msgctxt']);
			$this->assertStringEndsWith('src/Messages.php:2', $strings['Hello']['references'][0]);
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

			$poFile = $root . '/resources/locales/de/messages.po';
			file_put_contents($poFile, <<<'PO'
msgid ""
msgstr "Language: de\nPlural-Forms: nplurals=2; plural=(n != 1);\n"

msgid "Hello"
msgstr "Hallo"

msgid "Removed"
msgstr "Entfernt"
PO);
			$output = new StubConsoleOutput();
			$code = (new SyncCommand())->run(['--root', $root], new ConsoleIo($output, $output));
			$this->assertSame(0, $code, $output->output());
			$messages = (new Parser())->parseFile($poFile)->messages();
			$this->assertSame(['Hallo'], $messages["\x04Hello"]->msgstr);
			$this->assertSame('{0} items', $messages["count\x04One item"]->msgidPlural);
			$this->assertArrayNotHasKey("\x04Removed", $messages);
		} finally {
			(new Filesystem())->deleteDir($root);
		}
	}

}
