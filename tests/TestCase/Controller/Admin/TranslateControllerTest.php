<?php

namespace Translate\Test\TestCase\Controller\Admin;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use TestApp\Translator\Engine\Test;
use Translate\Test\TestCase\IntegrationTestCase;

/**
 * @uses \Translate\Controller\Admin\TranslateController
 */
class TranslateControllerTest extends IntegrationTestCase {

	/**
	 * Fixtures
	 *
	 * @var array<string>
	 */
	protected array $fixtures = [
		'plugin.Translate.TranslateProjects',
		'plugin.Translate.TranslateLocales',
		'plugin.Translate.TranslateDomains',
		'plugin.Translate.TranslateStrings',
		'plugin.Translate.TranslateTerms',
		'plugin.Translate.TranslateApiTranslations',
	];

	/**
	 * @return void
	 */
	public function testIndex() {
		$this->disableErrorHandlerMiddleware();

		$this->get(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'Translate', 'action' => 'index']);

		$this->assertResponseCode(200);
		$this->assertNoRedirect();

		// Verify all required view variables are set
		$this->assertNotNull($this->viewVariable('coverage'));
		$this->assertNotNull($this->viewVariable('languages'));
		$this->assertNotNull($this->viewVariable('count'));
		$this->assertNotNull($this->viewVariable('projectSwitchArray'));

		// Verify statistics structure
		$count = $this->viewVariable('count');
		if (is_array($count)) {
			$this->assertArrayHasKey('domains', $count);
			$this->assertArrayHasKey('strings', $count);
			$this->assertArrayHasKey('locales', $count);
			$this->assertArrayHasKey('translations', $count);
		}
	}

	/**
	 * Test index displays correct statistics
	 *
	 * @return void
	 */
	public function testIndexWithStatistics() {
		$this->disableErrorHandlerMiddleware();

		$this->get(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'Translate', 'action' => 'index']);

		$this->assertResponseCode(200);

		$count = $this->viewVariable('count');

		// Count can be 0 if no project session is set, or an array with statistics
		if (is_array($count)) {
			// Verify statistics calculation
			$this->assertGreaterThanOrEqual(0, $count['domains']);
			$this->assertGreaterThanOrEqual(0, $count['strings']);
			$this->assertGreaterThanOrEqual(0, $count['locales']);
			$this->assertGreaterThanOrEqual(0, $count['translations']);

			// Verify translations = strings * locales
			$expectedTranslations = $count['strings'] * $count['locales'];
			$this->assertSame($expectedTranslations, $count['translations']);
		} else {
			$this->assertSame(0, $count, 'Count should be 0 when no project session is set');
		}
	}

	/**
	 * Test index displays coverage information
	 *
	 * @return void
	 */
	public function testIndexWithCoverage() {
		$this->disableErrorHandlerMiddleware();

		$this->get(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'Translate', 'action' => 'index']);

		$this->assertResponseCode(200);

		$coverage = $this->viewVariable('coverage');
		$this->assertIsArray($coverage);

		// Coverage should be keyed by locale
		foreach ($coverage as $locale => $percentage) {
			$this->assertIsString($locale);
			$this->assertIsNumeric($percentage);
			$this->assertGreaterThanOrEqual(0, $percentage);
			$this->assertLessThanOrEqual(100, $percentage);
		}
	}

	/**
	 * @return void
	 */
	public function testReset() {
		$this->get(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'Translate', 'action' => 'reset']);

		$this->assertResponseCode(200);
		$this->assertNoRedirect();
	}

	/**
	 * @return void
	 */
	public function testResetPost() {
		$data = [
			'Form' => [
				'sel' => [
					'terms',
					'strings',
				],
			],
		];

		$this->post(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'Translate', 'action' => 'reset'], $data);

		$this->assertResponseCode(302);
		$this->assertRedirect();

		$TranslateStrings = TableRegistry::getTableLocator()->get('Translate.TranslateStrings');
		$this->assertSame(0, $TranslateStrings->find()->count());
		$this->assertSame(0, $TranslateStrings->TranslateTerms->find()->count());
	}

	/**
	 * The Test engine fakes translation and just strrev() the text for demo purposes.
	 * No API call then to any translation API.
	 *
	 * @return void
	 */
	public function testTranslate() {
		$this->disableErrorHandlerMiddleware();

		Router::extensions(['json']);
		Configure::write('Translate.engine', Test::class);

		$data = [
			'text' => 'Father',
			'from' => 'en',
			'to' => 'de',
		];
		$this->post(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'Translate', 'action' => 'translate', '_ext' => 'json'], $data);

		$this->assertResponseCode(200);
		$this->assertNoRedirect();

		$result = json_decode($this->_response->getBody(), true);
		$expected = [
			'translation' => 'rehtaF',
		];
		$this->assertSame($expected, $result);
	}

	/**
	 * Test bestPractice method
	 *
	 * @return void
	 */
	public function testBestPractice() {
		$this->get(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'Translate', 'action' => 'bestPractice']);

		$this->assertResponseCode(200);
		$this->assertNoRedirect();
	}

	/**
	 * Test convert method GET
	 *
	 * @return void
	 */
	public function testConvertGet() {
		$this->get(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'Translate', 'action' => 'convert']);

		$this->assertResponseCode(200);
		$this->assertNoRedirect();
	}

	/**
	 * Test convert method POST
	 *
	 * @return void
	 */
	public function testConvertPost() {
		$data = [
			'input' => 'Test text',
			'escape' => true,
		];
		$this->post(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'Translate', 'action' => 'convert'], $data);

		$this->assertResponseCode(200);
		$this->assertNoRedirect();
		$this->assertNotNull($this->viewVariable('text'));
	}

}
