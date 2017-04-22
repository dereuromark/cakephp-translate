<?php

namespace Translate\Test\TestCase\Yandex;

use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use Translate\Translator\Engine\Yandex;

/**
 */
class YandexTest extends TestCase {

	/**
	 * @var \Translate\Translator\Engine\Yandex
	 */
	protected $Yandex;

	public function setUp() {
		parent::setUp();

		$this->skipIf(!Configure::read('Yandex.key'), 'Requires API key');

		//Configure::write('Translate.engine', Yandex::class);
		$this->Yandex = new Yandex();
	}

	/**
	 * @return void
	 */
	public function testTranslate() {
		$text = 'Vater';
		$is = $this->Yandex->translate($text, 'de', 'en');

		$expected = 'Father';
		$this->assertSame($expected, $is);
	}

}
