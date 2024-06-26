<?php

namespace Translate\Test\TestCase\Translator\Engine;

use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use Translate\Translator\Engine\Yandex;

class YandexTest extends TestCase {

	/**
	 * @var \Translate\Translator\Engine\Yandex
	 */
	protected $Yandex;

	/**
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		$this->skipIf(!Configure::read('Yandex.key'), 'Requires API key');

		$this->Yandex = new Yandex();
	}

	/**
	 * @return void
	 */
	public function testTranslate() {
		$text = 'Father';
		$is = $this->Yandex->translate($text, 'de', 'en');

		$expected = 'Vater';
		$this->assertSame($expected, $is);
	}

}
