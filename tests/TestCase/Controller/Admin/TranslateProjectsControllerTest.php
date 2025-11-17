<?php

namespace Translate\Test\TestCase\Controller\Admin;

use Cake\ORM\TableRegistry;
use Translate\Test\TestCase\IntegrationTestCase;

/**
 * Translate\Controller\Admin\TranslateProjectsController Test Case
 *
 * @uses \Translate\Controller\Admin\TranslateProjectsController
 */
class TranslateProjectsControllerTest extends IntegrationTestCase {

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
	];

	/**
	 * Test index method
	 *
	 * @return void
	 */
	public function testIndex() {
		$this->get(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateProjects', 'action' => 'index']);

		$this->assertResponseCode(200);
		$this->assertNoRedirect();
	}

	/**
	 * Test view method
	 *
	 * @return void
	 */
	public function testView() {
		$id = 1;
		$this->get(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateProjects', 'action' => 'view', $id]);

		$this->assertResponseCode(200);
		$this->assertNoRedirect();
	}

	/**
	 * Test add method
	 *
	 * @return void
	 */
	public function testAdd() {
		$TranslateProjects = TableRegistry::getTableLocator()->get('Translate.TranslateProjects');
		$TranslateProjects->deleteAll([]);

		$this->get(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateProjects', 'action' => 'add']);

		$this->assertResponseCode(200);
		$this->assertNoRedirect();
	}

	/**
	 * Test edit method
	 *
	 * @return void
	 */
	public function testEdit() {
		$id = 1;
		$this->get(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateProjects', 'action' => 'edit', $id]);

		$this->assertResponseCode(200);
		$this->assertNoRedirect();
	}

	/**
	 * Test delete method
	 *
	 * @return void
	 */
	public function testDelete() {
		$id = 1;
		$this->post(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateProjects', 'action' => 'delete', $id]);

		$this->assertResponseCode(302);
		$this->assertRedirect();
	}

	/**
	 * Test switchProject method
	 *
	 * @return void
	 */
	public function testSwitchProject() {
		$this->enableRetainFlashMessages();

		$data = ['project_switch' => 1];
		$this->post(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateProjects', 'action' => 'switchProject'], $data);

		$this->assertResponseCode(302);
		$this->assertRedirect(['plugin' => 'Translate', 'controller' => 'Translate', 'action' => 'index']);
		$this->assertFlashMessage('Project switched');
		$this->assertSession(1, 'TranslateProject.id');
	}

	/**
	 * Test reset method GET
	 *
	 * @return void
	 */
	public function testResetGet() {
		$this->get(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateProjects', 'action' => 'reset']);

		$this->assertResponseCode(200);
		$this->assertNoRedirect();
	}

	/**
	 * Test reset method POST
	 *
	 * @return void
	 */
	public function testResetPost() {
		$this->disableErrorHandlerMiddleware();
		$this->enableRetainFlashMessages();

		$this->session(['TranslateProject.id' => 1]);

		$data = [
			'Form' => [
				'reset' => ['terms'],
				'language' => [1],
			],
		];
		$this->post(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateProjects', 'action' => 'reset'], $data);

		$this->assertResponseCode(200);
		$this->assertNoRedirect();
		$this->assertFlashMessage('Done');
	}

}
