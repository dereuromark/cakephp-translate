<?php

namespace Translate\Test\TestCase\Lib;

use Cake\Filesystem\Folder;
use Cake\TestSuite\TestCase;
use Translate\Lib\TranslationLib;

class TranslationLibTest extends TestCase {

	/**
	 * @var \Translate\Lib\TranslationLib
	 */
	protected $TranslationLib;

	/**
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();
		$this->TranslationLib = new TranslationLib();

		$folder = new Folder();
		$folder->copy(LOCALE, [
			'from' => ROOT . DS . 'tests' . DS . 'test_files' . DS . 'Locale' . DS,
		]);
	}

	/**
	 * @return void
	 */
	public function testGetPotFiles() {
		$is = $this->TranslationLib->getPotFiles();
		$this->assertTrue(!empty($is));
		$expected = [
			'cake' => 'cake',
			'default' => 'default',
		];
		$this->assertSame($expected, $is);
	}

	/**
	 * @return void
	 */
	public function testExtractPoFileLanguages() {
		$is = $this->TranslationLib->getPoFileLanguages();
		$expected = [
			'de',
		];
		$this->assertSame($expected, $is);
	}

	/**
	 * @return void
	 */
	public function testGetPoFiles() {
		$is = $this->TranslationLib->getPoFiles('de');

		$this->assertTrue(!empty($is));
		$expected = [
			'de_cake' => 'cake',
			'de_default' => 'default',
		];
		$this->assertSame($expected, $is);
	}

	/**
	 * @return void
	 */
	public function testExtractPotFile() {
		$is = $this->TranslationLib->getPotFiles();
		$file = array_shift($is);
		$is = $this->TranslationLib->extractPotFile($file);

		$this->assertTrue(!empty($is));

		$lastTranslation = array_pop($is);

		$expected = ['name', 'content', 'comments', 'references'];
		$this->assertSame($expected, array_keys($lastTranslation));
	}

	/**
	 * @return void
	 */
	public function testExtractPoFile() {
		$is = $this->TranslationLib->getPoFiles('de');
		$file = array_pop($is);
		$is = $this->TranslationLib->extractPoFile($file, 'de');

		$this->assertTrue(!empty($is));

		$expected = [
			'name' => '{0} tree',
			'content' => '{0} Baum',
			'comments' => null,
			'plural' => '{0} trees',
			'plural_2' => '{0} Bäume',
			'context' => 'context',
			'flags' => ['fuzzy', 'special'],
		];
		$this->assertSame($expected, $is[6], print_r($is[6], true));
	}

	/**
	 * @return void
	 */
	public function testParsePotFile() {
		$is = $this->TranslationLib->getPotFiles();
		$file = array_shift($is);
		$is = $this->TranslationLib->parseFile(LOCALE . $file . '.pot');

		$this->assertTrue(!empty($is));
		$this->assertArrayHasKey('December', $is);
		$expected = [
			'_context' => [
				'' => [
					0 => '',
					1 => '',
				],
			],
		];
		$this->assertSame($expected, $is['p:{0} years']);
	}

	/**
	 * @return void
	 */
	public function testParsePoFile() {
		$is = $this->TranslationLib->getPoFiles('de');
		$file = array_pop($is);
		$is = $this->TranslationLib->parseFile(LOCALE . 'de' . DS . $file . '.po');

		$this->assertTrue(!empty($is));
		$this->assertArrayHasKey('Your {0}.', $is);

		$expected = [
			'_context' => [
				'context' => 'translated-string',
			],
		];
		$this->assertSame($expected, $is['untranslated-string']);

		$expected = [
			'_context' => [
				'context' => [
					'{0} Baum',
					'{0} Bäume',
				],
			],
		];
		$this->assertSame($expected, $is['p:{0} trees']);
	}

	/**
	 * @return void
	 */
	public function testParsePoFilePlural() {
		$is = $this->TranslationLib->getPoFiles('de');
		$file = array_shift($is);
		$is = $this->TranslationLib->parseFile(LOCALE . 'de' . DS . $file . '.po');

		$this->assertTrue(!empty($is));
		$expected = [
			'Error' => [
				'_context' => [
					'' => 'Fehler',
				],
			],
			'The requested address {0} was not found on this server.' => [
				'_context' => [
					'' => 'Die Adresse {0} wurde nicht gefunden.',
				],
			],
			'{0} year' => [
				'_context' => [
					'' => '{0} Jahr',
				],
			],
			'p:{0} years' => [
				'_context' => [
					'' => [
						0 => '{0} Jahr',
						1 => '{0} Jahre',
					],
				],
			],
		];
		$this->assertSame($expected, $is);
	}

	/**
	 * @return void
	 */
	public function testResourceNames() {
		$is = $this->TranslationLib->getResourceNames();

		//$this->assertTrue(!empty($is));
		$this->assertSame([], $is);
	}

}
