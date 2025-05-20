<?php

namespace Translate\Test\TestCase\Controller\Admin;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Shim\TestSuite\IntegrationTestCase;
use TestApp\Translator\Engine\Test;

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
		'plugin.Translate.TranslateLanguages',
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

}
