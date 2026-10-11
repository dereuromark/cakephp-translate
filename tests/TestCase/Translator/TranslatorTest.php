<?php

namespace Translate\Test\TestCase\Translator;

use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use RuntimeException;
use TestApp\Translator\Engine\Retry;
use TestApp\Translator\Engine\Test;
use TestApp\Translator\Engine\TestMore;
use Translate\Model\Table\TranslateApiTranslationsTable;
use Translate\Translator\Translator;

class TranslatorTest extends TestCase {

	/**
	 * @var \Translate\Translator\Translator
	 */
	protected $Translator;

	protected array $fixtures = ['plugin.Translate.TranslateApiTranslations'];

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
			Test::class => 'retaV',
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
			Test::class => 'retaV',
			TestMore::class => 'vater',
		];
		$this->assertSame($expected, $is);
	}

	public function testFailedTranslationIsRetriedAndSuccessIsCached(): void {
		Retry::$calls = 0;
		$translator = new Translator(['engine' => Retry::class]);
		$this->assertNull($translator->translate('Temporary failure', 'de', 'en'));
		$this->assertSame('Recovered', $translator->translate('Temporary failure', 'de', 'en'));
		$this->assertSame('Recovered', $translator->translate('Temporary failure', 'de', 'en'));
		$this->assertSame(2, Retry::$calls);
	}

	public function testLegacyNullCacheEntryDoesNotPreventRetry(): void {
		Retry::$calls = 1;
		$cache = $this->getTableLocator()->get('Translate.TranslateApiTranslations');
		$cache->getConnection()->insert($cache->getTable(), [
			'key' => 'Legacy failure',
			'value' => null,
			'from' => 'en',
			'to' => 'de',
			'engine' => Retry::class,
		]);
		$translator = new Translator(['engine' => Retry::class]);
		$this->assertSame('Recovered', $translator->translate('Legacy failure', 'de', 'en'));
		$this->assertSame('Recovered', $cache->retrieve('Legacy failure', 'de', 'en', Retry::class)->value);
	}

	public function testCacheWriteFailureKeepsTheProviderResult(): void {
		$cache = $this->getMockBuilder(TranslateApiTranslationsTable::class)->onlyMethods(['retrieve', 'store'])->getMock();
		$cache->expects($this->once())->method('retrieve')->willReturn(null);
		$cache->expects($this->once())->method('store')->willThrowException(new RuntimeException('Cache write failed'));
		$this->getTableLocator()->set('Translate.TranslateApiTranslations', $cache);
		$translator = new Translator(['engine' => Test::class]);
		$this->assertSame('olleH', $translator->translate('Hello', 'de', 'en'));
	}

}
