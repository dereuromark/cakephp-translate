<?php

namespace Translate\Test\TestCase\Translator;

use App\Translator\Engine\Test;
use App\Translator\Engine\TestMore;
use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use Translate\Translator\Translator;

class TranslatorTest extends TestCase {

	/**
	 * @var \Translate\Translator\Translator
	 */
	protected $Translator;

	/**
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		Configure::write('Translate.engine', Test::class);

		$this->Translator = new Translator();
	}

	/**
	 * @return void
	 */
	public function testTranslate() {
		$text = 'Vater';
		$is = $this->Translator->translate($text, 'de', 'en');

		$expected = 'retaV';
		$this->assertSame($expected, $is);
	}

	/**
	 * @return void
	 */
	public function testSuggest() {
		$text = 'Vater';
		$is = $this->Translator->suggest($text, 'de', 'en');

		$expected = [
			'App\Translator\Engine\Test' => 'retaV',
		];
		$this->assertSame($expected, $is);
	}

	/**
	 * @return void
	 */
	public function testSuggestMulti() {
		Configure::write('Translate.engine', [Test::class, TestMore::class]);
		$this->Translator = new Translator();

		$text = 'Vater';
		$is = $this->Translator->suggest($text, 'de', 'en');

		$expected = [
			'App\Translator\Engine\Test' => 'retaV',
			'App\Translator\Engine\TestMore' => 'vater',
		];
		$this->assertSame($expected, $is);
	}

}
