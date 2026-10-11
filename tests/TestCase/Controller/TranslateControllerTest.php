<?php

namespace Translate\Test\TestCase\Controller;

use Cake\Core\Configure;
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
		$this->session(['TranslateProject' => ['id' => 1]]);
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
		$this->session(['TranslateProject' => ['id' => $project->id]]);
		$this->get('/translate/translate/translate/default/1');
		$this->assertResponseCode(404);
	}

	public function testPublicIndexSelectsPublicProjectWhenDefaultIsHidden(): void {
		$projects = $this->getTableLocator()->get('Translate.TranslateProjects');
		$project = $projects->newEntity(['name' => 'Public catalog', 'type' => TranslateProject::TYPE_APP, 'default' => false, 'status' => TranslateProject::STATUS_PUBLIC]);
		$projects->saveOrFail($project);
		Configure::delete('Translate.adminAccess');
		$this->get('/translate');
		$this->assertResponseCode(200);
		$this->assertSession($project->id, 'TranslateProject.id');
	}

	public function testPublicIndexRecoversFromDeletedProject(): void {
		$projects = $this->getTableLocator()->get('Translate.TranslateProjects');
		$project = $projects->get(1);
		$project->status = TranslateProject::STATUS_PUBLIC;
		$projects->saveOrFail($project);
		Configure::delete('Translate.adminAccess');
		$this->session(['TranslateProject' => ['id' => 999]]);
		$this->get('/translate');
		$this->assertResponseCode(200);
		$this->assertSession(1, 'TranslateProject.id');
	}

}
