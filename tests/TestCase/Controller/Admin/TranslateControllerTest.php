<?php

namespace Translate\Test\TestCase\Controller\Admin;

use App\Translator\Engine\Test;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\TestSuite\IntegrationTestCase;

/**
 * @uses \Translate\Controller\Admin\TranslateController
 */
class TranslateControllerTest extends IntegrationTestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'plugin.Translate.TranslateProjects',
		'plugin.Translate.TranslateLanguages',
		'plugin.Translate.TranslateDomains',
		'plugin.Translate.TranslateStrings',
		'plugin.Translate.TranslateTerms',
	];

	/**
	 * @return void
	 */
	public function testIndex() {
		$this->get(['prefix' => 'admin', 'plugin' => 'Translate', 'controller' => 'Translate', 'action' => 'index']);

		$this->assertResponseCode(200);
		$this->assertNoRedirect();
	}

	/**
	 * @return void
	 */
	public function testReset() {
		$this->get(['prefix' => 'admin', 'plugin' => 'Translate', 'controller' => 'Translate', 'action' => 'reset']);

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

		$this->post(['prefix' => 'admin', 'plugin' => 'Translate', 'controller' => 'Translate', 'action' => 'reset'], $data);

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
		Router::extensions(['json']);
		Configure::write('Translate.engine', Test::class);

		$data = [
			'text' => 'Father',
			'from' => 'en',
			'to' => 'de',
		];
		$this->post(['prefix' => 'admin', 'plugin' => 'Translate', 'controller' => 'Translate', 'action' => 'translate', '_ext' => 'json'], $data);

		$this->assertResponseCode(200);
		$this->assertNoRedirect();

		$result = json_decode($this->_response->body(), true);
		$expected = [
			'translation' => 'rehtaF',
		];
		$this->assertSame($expected, $result);
	}

}
