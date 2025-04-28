<?php

namespace Translate\Test\TestCase\Service;

use Cake\TestSuite\TestCase;
use Shim\Filesystem\Folder;
use Translate\Service\ExtractService;

class ExtractServiceTest extends TestCase {

	/**
	 * @var \Translate\Service\ExtractService
	 */
	protected $service;

	/**
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();
		$this->service = new ExtractService();

		$folder = new Folder();
		$folder->copy(LOCALE, [
			'from' => ROOT . DS . 'tests' . DS . 'test_files' . DS . 'Locale' . DS,
		]);
	}

	/**
	 * @return void
	 */
	public function testGetPotFiles() {
		$is = $this->service->getPotFiles();
		$this->assertTrue(!empty($is));
		$expected = [
			'cake' => 'cake',
			'default' => 'default',
			'translate' => 'translate',
		];
		$this->assertSame($expected, $is);
	}

	/**
	 * @return void
	 */
	public function testExtractPoFileLanguages() {
		$is = $this->service->getPoFileLanguages();
		$expected = [
			'de',
		];
		$this->assertSame($expected, $is);
	}

	/**
	 * @return void
	 */
	public function testGetPoFiles() {
		$is = $this->service->getPoFiles('de');

		$this->assertTrue(!empty($is));
		$expected = [
			'de-cake' => 'cake',
			'de-default' => 'default',
		];
		$this->assertSame($expected, $is);
	}

	/**
	 * @return void
	 */
	public function testExtractPotFile() {
		$is = $this->service->getPotFiles();
		$file = array_shift($is);
		$is = $this->service->extractPotFile($file);

		$this->assertTrue(!empty($is));

		$lastTranslation = array_pop($is);

		$expected = ['name', 'content', 'comments', 'references'];
		$this->assertSame($expected, array_keys($lastTranslation));
	}

	/**
	 * @return void
	 */
	public function testExtractPoFile() {
		$is = $this->service->getPoFiles('de');
		$file = array_pop($is);
		$is = $this->service->extractPoFile($file, 'de');

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
		$is = $this->service->getPotFiles();
		$file = array_shift($is);
		$is = $this->service->parseFile(LOCALE . $file . '.pot');

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
		$is = $this->service->getPoFiles('de');
		$file = array_pop($is);
		$is = $this->service->parseFile(LOCALE . 'de' . DS . $file . '.po');

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
		$is = $this->service->getPoFiles('de');
		$file = array_shift($is);
		$is = $this->service->parseFile(LOCALE . 'de' . DS . $file . '.po');

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
		$is = $this->service->getResourceNames();

		//$this->assertTrue(!empty($is));
		$this->assertSame([], $is);
	}

}
