<?php

namespace Translate\Test\TestCase\Translator\Engine;

use Cake\TestSuite\TestCase;
use ReflectionMethod;
use Translate\Translator\Engine\GoogleTranslate;

class GoogleTranslateTest extends TestCase {

	public function testMalformedProviderResponsesReturnEmptyText(): void {
		$extractor = new ReflectionMethod(GoogleTranslate::class, 'getSentencesFromJSON');
		$this->assertSame('', $extractor->invoke(null, ''));
		$this->assertSame('', $extractor->invoke(null, '{"error":"unavailable"}'));
		$this->assertSame('', $extractor->invoke(null, '{"sentences":[null,1,{"trans":null}]}'));
		$this->assertSame('Hallo Welt', $extractor->invoke(null, '{"sentences":[{"trans":"Hallo "},{"trans":"Welt"}]}'));
	}

}
