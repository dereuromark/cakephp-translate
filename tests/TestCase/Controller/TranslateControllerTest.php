<?php

namespace Translate\Test\TestCase\Controller;

use Cake\Core\Configure;
use PHPUnit\Framework\Attributes\DataProvider;
use Translate\Model\Entity\TranslateProject;
use Translate\Test\TestCase\IntegrationTestCase;

class TranslateControllerTest extends IntegrationTestCase {

	protected array $fixtures = [
		'plugin.Translate.TranslateProjects',
		'plugin.Translate.TranslateDomains',
		'plugin.Translate.TranslateStrings',
		'plugin.Translate.TranslateTerms',
		'plugin.Translate.TranslateLocales',
	];

	public function testHiddenProjectRequiresAccess(): void {
		Configure::delete('Translate.adminAccess');
		$this->session(['TranslatePublicProject' => ['id' => 1]]);
		$this->get('/translate/translate/index');
		$this->assertResponseCode(403);
	}

	public function testDisabledCodeReferencesAreNotReadable(): void {
		Configure::write('Translate.showCodeReferences', false);
		$this->get('/translate/translate/display-reference/1/0');
		$this->assertResponseCode(404);
	}

	public function testSwitchProjectRequiresPost(): void {
		$this->get('/translate/translate/switch-project');
		$this->assertResponseCode(405);
	}

	public function testSwitchingToHiddenProjectRequiresAccess(): void {
		Configure::delete('Translate.adminAccess');
		$this->post('/translate/translate/switch-project', ['project_switch' => 1]);
		$this->assertResponseCode(403);
	}

	public function testPublicProjectCannotReadAnotherProjectsString(): void {
		$projects = $this->getTableLocator()->get('Translate.TranslateProjects');
		$project = $projects->newEntity([
			'name' => 'Public catalog',
			'type' => TranslateProject::TYPE_APP,
			'default' => false,
			'status' => TranslateProject::STATUS_PUBLIC,
		]);
		$projects->saveOrFail($project);
		Configure::delete('Translate.adminAccess');
		$this->session(['TranslatePublicProject' => ['id' => $project->id]]);
		$this->get('/translate/translate/translate/default/1');
		$this->assertResponseCode(404);
	}

	public function testPublicIndexSelectsPublicProjectWhenDefaultIsHidden(): void {
		$projects = $this->getTableLocator()->get('Translate.TranslateProjects');
		$project = $projects->newEntity(['name' => 'Public catalog', 'type' => TranslateProject::TYPE_APP, 'default' => false, 'status' => TranslateProject::STATUS_PUBLIC]);
		$projects->saveOrFail($project);
		Configure::delete('Translate.adminAccess');
		$this->session(['TranslateProject' => ['id' => 1]]);
		$this->get('/translate');
		$this->assertResponseCode(200);
		$this->assertSession(1, 'TranslateProject.id');
		$this->assertSession($project->id, 'TranslatePublicProject.id');
	}

	public function testPublicIndexRecoversFromDeletedProject(): void {
		$projects = $this->getTableLocator()->get('Translate.TranslateProjects');
		$project = $projects->get(1);
		$project->status = TranslateProject::STATUS_PUBLIC;
		$projects->saveOrFail($project);
		Configure::delete('Translate.adminAccess');
		$this->session(['TranslatePublicProject' => ['id' => 999]]);
		$this->get('/translate');
		$this->assertResponseCode(200);
		$this->assertSession(1, 'TranslatePublicProject.id');
	}

	protected function createPublicCatalog(): array {
		$projects = $this->fetchTable('Translate.TranslateProjects');
		$project = $projects->newEntity(['name' => 'Public catalog', 'type' => TranslateProject::TYPE_APP, 'default' => false, 'status' => TranslateProject::STATUS_PUBLIC]);
		$projects->saveOrFail($project);
		$domains = $this->fetchTable('Translate.TranslateDomains');
		$domain = $domains->newEntity(['name' => 'public_catalog', 'translate_project_id' => $project->id, 'active' => true, 'prio' => 1]);
		$domains->saveOrFail($domain);
		$strings = $this->fetchTable('Translate.TranslateStrings');
		$string = $strings->newEntity(['name' => 'Public greeting', 'translate_domain_id' => $domain->id, 'active' => true, 'is_html' => false, 'skipped' => false, 'references' => null]);
		$strings->saveOrFail($string);
		$locales = $this->fetchTable('Translate.TranslateLocales');
		$locale = $locales->newEntity(['name' => 'German', 'locale' => 'de_DE', 'iso2' => 'de', 'translate_project_id' => $project->id, 'active' => true]);
		$locales->saveOrFail($locale);

		return [$project->id, $string->id];
	}

	public function testDirectPublicTermsFallsBackBeforeAccessCheck(): void {
		[$projectId] = $this->createPublicCatalog();
		Configure::delete('Translate.adminAccess');
		$this->get('/translate/translate/terms');
		$this->assertResponseOk();
		$this->assertResponseContains('Public greeting');
		$this->assertResponseContains('title="de_DE"');
		$this->assertResponseNotContains('title="en_US"');
		$this->assertSession($projectId, 'TranslatePublicProject.id');
		$this->assertSession(null, 'TranslateProject.id');
		$this->assertSame($projectId, $this->viewVariable('currentProject')->get('id'));
	}

	public function testDirectPublicTranslateFallsBackBeforeAccessCheck(): void {
		[$projectId, $stringId] = $this->createPublicCatalog();
		Configure::delete('Translate.adminAccess');
		$this->session(['TranslateProject' => ['id' => 777]]);
		$this->get('/translate/translate/translate/public_catalog/' . $stringId);
		$this->assertResponseOk();
		$this->assertResponseContains('Public greeting');
		$this->assertResponseContains('title="de_DE"');
		$this->assertResponseNotContains('title="en_US"');
		$this->assertSession($projectId, 'TranslatePublicProject.id');
		$this->assertSession(777, 'TranslateProject.id');
	}

	public static function authorizedSelections(): array {
		return [
			'hidden overview' => [TranslateProject::STATUS_HIDDEN, '/translate'],
			'hidden terms' => [TranslateProject::STATUS_HIDDEN, '/translate/translate/terms'],
			'hidden translate' => [TranslateProject::STATUS_HIDDEN, '/translate/translate/translate/default/1'],
			'inactive overview' => [TranslateProject::STATUS_INACTIVE, '/translate'],
			'inactive terms' => [TranslateProject::STATUS_INACTIVE, '/translate/translate/terms'],
			'inactive translate' => [TranslateProject::STATUS_INACTIVE, '/translate/translate/translate/default/1'],
		];
	}

	#[DataProvider('authorizedSelections')]
	public function testAuthorizedPrivateSelectionIsPreserved(int $status, string $url): void {
		$this->createPublicCatalog();
		$projects = $this->fetchTable('Translate.TranslateProjects');
		$projects->updateAll(['status' => $status], ['id' => 1]);
		$checks = 0;
		Configure::write('Translate.adminAccess', function () use (&$checks): bool {
			$checks++;

			return true;
		});
		$this->session(['TranslatePublicProject' => ['id' => 1], 'TranslateProject' => ['id' => 777]]);
		$this->get($url);
		$this->assertResponseOk();
		$this->assertSession(1, 'TranslatePublicProject.id');
		$this->assertSession(777, 'TranslateProject.id');
		$this->assertSame(1, $this->viewVariable('currentProject')->get('id'));
		$this->assertSame(1, $checks);
	}

	public function testUnauthorizedHiddenSelectionFallsBackToPublic(): void {
		[$projectId] = $this->createPublicCatalog();
		$checks = 0;
		Configure::write('Translate.adminAccess', function () use (&$checks): bool {
			$checks++;

			return false;
		});
		$this->session(['TranslatePublicProject' => ['id' => 1], 'TranslateProject' => ['id' => 777]]);
		$this->get('/translate/translate/terms');
		$this->assertResponseOk();
		$this->assertResponseContains('Public greeting');
		$this->assertSession($projectId, 'TranslatePublicProject.id');
		$this->assertSession(777, 'TranslateProject.id');
		$this->assertSame(1, $checks);
	}

	public function testPublicSelectionDoesNotInvokePrivateAccessGate(): void {
		[$projectId] = $this->createPublicCatalog();
		$checks = 0;
		Configure::write('Translate.adminAccess', function () use (&$checks): bool {
			$checks++;

			return false;
		});
		$this->session(['TranslatePublicProject' => ['id' => $projectId], 'TranslateProject' => ['id' => 777]]);
		$this->get('/translate/translate/terms');
		$this->assertResponseOk();
		$this->assertSession($projectId, 'TranslatePublicProject.id');
		$this->assertSession(777, 'TranslateProject.id');
		$this->assertSame(0, $checks);
	}

}
