<?php

namespace Translate\Test\TestCase\Parser;

use Cake\I18n\Parser\PoFileParser;
use Cake\TestSuite\TestCase;
use Exception;
use Translate\Parser\PoParser;
use Translate\Parser\Writer;

class WriterTest extends TestCase {

	public function testMultilineCommentsAndControlCharactersRoundTrip(): void {
		$file = tempnam(TMP, 'writer_');
		try {
			$writer = new Writer();
			$writer->write($file, [
				[
					'msgid' => 'Message',
					'msgstr' => "Line\r\nTab\tquote\"slash\\",
					'tcomment' => "First\r\nSecond\rThird",
					'ccomment' => "Source\nDetail",
				],
			]);
			$content = file_get_contents($file);
			$this->assertStringContainsString("# First\n# Second\n# Third\n#. Source\n#. Detail\n", $content);
			$this->assertStringNotContainsString("\r", $content);
			$this->assertSame("Line\r\nTab\tquote\"slash\\", (new PoFileParser())->parse($file)['Message']['_context']['']);
			$parser = new PoParser();
			$parser->read($file);
			$entry = array_values($parser->getEntriesAsArrays())[0];
			$this->assertSame("First\nSecond\nThird", $entry['tcomment']);
			$this->assertSame("Source\nDetail", $entry['ccomment']);
		} finally {
			unlink($file);
		}
	}

	public function testFailedWritePreservesExistingCatalog(): void {
		$file = tempnam(TMP, 'writer_');
		file_put_contents($file, 'existing catalog');
		$before = glob(TMP . '.translate_*');
		$writer = new class extends Writer {
			/**
             * @return void
             */
			protected function writeToHandle($handle, string $content): void {
				parent::writeToHandle($handle, $content);

				throw new Exception('Simulated write failure');
			}
		};
		try {
			try {
				$writer->write($file, [['msgid' => 'Message', 'msgstr' => 'Translation']]);
				$this->fail('Expected write failure');
			} catch (Exception $e) {
				$this->assertSame('Simulated write failure', $e->getMessage());
			}
			$this->assertSame('existing catalog', file_get_contents($file));
			$this->assertSame($before, glob(TMP . '.translate_*'));
		} finally {
			unlink($file);
		}
	}

	public function testWriteFailureThrows(): void {
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Unable to open file');
		(new Writer())->write(TMP, []);
	}

	public function testFullDeviceThrows(): void {
		if (!file_exists('/dev/full')) {
			$this->markTestSkipped('/dev/full is unavailable.');
		}
		$this->expectException(Exception::class);
		(new Writer())->write('/dev/full', [['msgid' => 'Message', 'msgstr' => 'Translation']]);
	}

}
