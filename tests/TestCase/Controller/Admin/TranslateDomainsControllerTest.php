<?php

namespace Translate\Test\TestCase\Controller\Admin;

use Translate\Test\TestCase\IntegrationTestCase;

/**
 * Translate\Controller\Admin\TranslateDomainsController Test Case
 *
 * @uses \Translate\Controller\Admin\TranslateDomainsController
 */
class TranslateDomainsControllerTest extends IntegrationTestCase {

	/**
	 * Fixtures
	 *
	 * @var array<string>
	 */
	protected array $fixtures = [
		'plugin.Translate.TranslateDomains',
		'plugin.Translate.TranslateStrings',
		'plugin.Translate.TranslateProjects',
	];

	/**
	 * Test index method
	 *
	 * @return void
	 */
	public function testIndex() {
		$this->disableErrorHandlerMiddleware();

		$this->get(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateDomains', 'action' => 'index']);

		$this->assertResponseCode(200);
		$this->assertNoRedirect();
	}

	/**
	 * Test view method
	 *
	 * @return void
	 */
	public function testView() {
		$this->disableErrorHandlerMiddleware();

		$id = 1;
		$this->get(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateDomains', 'action' => 'view', $id]);

		$this->assertResponseCode(200);
		$this->assertNoRedirect();
	}

	/**
	 * Test add method
	 *
	 * @return void
	 */
	public function testAdd() {
		$this->disableErrorHandlerMiddleware();

		$this->get(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateDomains', 'action' => 'add']);

		$this->assertResponseCode(200);
		$this->assertNoRedirect();
	}

	/**
	 * Test edit method
	 *
	 * @return void
	 */
	public function testEdit() {
		$this->disableErrorHandlerMiddleware();

		$id = 1;
		$this->get(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateDomains', 'action' => 'edit', $id]);

		$this->assertResponseCode(200);
		$this->assertNoRedirect();
	}

	/**
	 * Test delete method
	 *
	 * @return void
	 */
	public function testDelete() {
		$this->disableErrorHandlerMiddleware();

		$id = 1;
		$this->post(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateDomains', 'action' => 'delete', $id]);

		$this->assertResponseCode(302);
		$this->assertRedirect();
	}

	/**
	 * Test index only shows domains for current project
	 *
	 * @return void
	 */
	public function testIndexOnlyShowsCurrentProjectDomains() {
		$this->disableErrorHandlerMiddleware();

		// Create a domain for a different project
		$TranslateDomains = $this->fetchTable('Translate.TranslateDomains');
		$otherDomain = $TranslateDomains->newEntity([
			'name' => 'other-project-domain',
			'translate_project_id' => 999,
			'active' => true,
		]);
		$TranslateDomains->save($otherDomain);

		// Access index with project 1 selected
		$this->session(['TranslateProject.id' => 1]);
		$this->get(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateDomains', 'action' => 'index']);

		$this->assertResponseCode(200);

		/** @var \Cake\ORM\ResultSet<\Translate\Model\Entity\TranslateDomain> $domains */
		$domains = $this->viewVariable('translateDomains');
		foreach ($domains as $domain) {
			$this->assertSame(1, $domain->translate_project_id, 'All domains should belong to project 1');
		}
	}

	/**
	 * Test view returns 404 for domain from different project
	 *
	 * @return void
	 */
	public function testViewDomainFromOtherProjectReturns404() {
		// Create a domain for a different project
		$TranslateDomains = $this->fetchTable('Translate.TranslateDomains');
		$otherDomain = $TranslateDomains->newEntity([
			'name' => 'other-project-domain',
			'translate_project_id' => 999,
			'active' => true,
		]);
		$TranslateDomains->save($otherDomain);

		// Access with project 1 selected
		$this->session(['TranslateProject.id' => 1]);
		$this->get(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'TranslateDomains', 'action' => 'view', $otherDomain->id]);

		$this->assertResponseCode(404);
	}

}
