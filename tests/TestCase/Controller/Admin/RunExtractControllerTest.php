<?php

namespace Translate\Test\TestCase\Controller\Admin;

use PHPUnit\Framework\Attributes\DataProvider;
use Translate\Filesystem\Folder;
use Translate\Model\Entity\TranslateProject;
use Translate\Test\TestCase\IntegrationTestCase;

class RunExtractControllerTest extends IntegrationTestCase {

	protected array $fixtures = [
		'plugin.Translate.TranslateStrings',
		'plugin.Translate.TranslateDomains',
		'plugin.Translate.TranslateLocales',
		'plugin.Translate.TranslateProjects',
		'plugin.Translate.TranslateTerms',
		'plugin.Translate.Users',
	];

	protected string $projectPath;

	protected function setUp(): void {
		parent::setUp();
		$this->projectPath = TMP . 'extract_project';
		mkdir($this->projectPath . '/src', 0770, true);
		mkdir($this->projectPath . '/resources/locales', 0770, true);
		file_put_contents($this->projectPath . '/src/Messages.php', "<?php\n__('Greeting');\n__d('example', 'Domain greeting');\n");
		$this->fetchTable('Translate.TranslateProjects')->updateAll(['path' => $this->projectPath], ['id' => 1]);
		$this->session(['TranslateProject.id' => 1]);
		$this->enableRetainFlashMessages();
	}

	protected function tearDown(): void {
		(new Folder())->delete($this->projectPath);
		if (is_link(TMP . 'extract_project_link')) {
			unlink(TMP . 'extract_project_link');
		}
		parent::tearDown();
	}

	public static function extractionOptions(): array {
		return [
			'separate domains' => [false, true, false],
			'combined domains' => [true, true, false],
			'preserve existing' => [false, false, false],
			'preview combined' => [true, false, true],
		];
	}

	#[DataProvider('extractionOptions')]
	public function testExtractionOptions(bool $merge, bool $overwrite, bool $dryRun): void {
		$file = $this->projectPath . '/resources/locales/default.pot';
		file_put_contents($file, 'existing content');
		$before = glob(sys_get_temp_dir() . '/translate_extract_*');
		$this->post('/admin/translate/translate-strings/run-extract', [
			'paths' => 'src',
			'merge' => $merge,
			'overwrite' => $overwrite,
			'dry_run' => $dryRun,
		]);
		$this->assertResponseOk();
		$this->assertSame(0, $this->viewVariable('returnCode'));
		$this->assertSame($before, glob(sys_get_temp_dir() . '/translate_extract_*'));
		if ($dryRun || !$overwrite) {
			$this->assertSame('existing content', file_get_contents($file));
		} else {
			$this->assertStringContainsString('Greeting', file_get_contents($file));
		}
		if ($dryRun) {
			$this->assertStringContainsString('Domain greeting', $this->viewVariable('dryRunResults')['default.pot']['content']);
		} elseif ($merge) {
			$this->assertStringContainsString('Domain greeting', file_get_contents($file));
			$this->assertFileDoesNotExist($this->projectPath . '/resources/locales/example.pot');
		} else {
			$this->assertFileExists($this->projectPath . '/resources/locales/example.pot');
		}
	}

	public function testSymlinkedProjectSupportsRelativeOutput(): void {
		symlink($this->projectPath, TMP . 'extract_project_link');
		$this->fetchTable('Translate.TranslateProjects')->updateAll(['path' => TMP . 'extract_project_link'], ['id' => 1]);
		$this->post('/admin/translate/translate-strings/run-extract', ['paths' => 'src', 'output_path' => 'new/locales', 'overwrite' => true]);
		$this->assertResponseOk();
		$this->assertSame(0, $this->viewVariable('returnCode'));
		$this->assertFileExists($this->projectPath . '/new/locales/default.pot');
	}

	public function testOutputSymlinkCannotEscapeProject(): void {
		symlink(sys_get_temp_dir(), $this->projectPath . '/outside');
		$this->post('/admin/translate/translate-strings/run-extract', ['paths' => 'src', 'output_path' => 'outside/new_locales']);
		$this->assertResponseOk();
		$this->assertNull($this->viewVariable('returnCode'));
		$this->assertFlashMessageContains('Output path must be inside the project');
		unlink($this->projectPath . '/outside');
	}

	public function testInvalidPluginPathsRenderError(): void {
		$this->fetchTable('Translate.TranslateProjects')->updateAll(['type' => TranslateProject::TYPE_PLUGIN, 'name' => 'Example'], ['id' => 1]);
		$this->post('/admin/translate/translate-strings/run-extract', ['paths' => '/missing/path']);
		$this->assertResponseOk();
		$this->assertSame('example', $this->viewVariable('pluginDomain'));
		$this->assertFlashMessageContains('No valid paths');
	}

	public function testPluginKeepsOwnDomainWhenMergePosted(): void {
		$this->fetchTable('Translate.TranslateProjects')->updateAll(['type' => TranslateProject::TYPE_PLUGIN, 'name' => 'Example'], ['id' => 1]);
		$this->post('/admin/translate/translate-strings/run-extract', ['paths' => 'src', 'merge' => true, 'overwrite' => true]);
		$this->assertResponseOk();
		$this->assertSame(0, $this->viewVariable('returnCode'));
		$this->assertFileExists($this->projectPath . '/resources/locales/example.pot');
		$this->assertFileDoesNotExist($this->projectPath . '/resources/locales/default.pot');
	}

	public function testBlockedOutputPathLeavesNoTemporaryDirectory(): void {
		file_put_contents($this->projectPath . '/blocker', 'file');
		$before = glob(sys_get_temp_dir() . '/translate_extract_*');
		$this->post('/admin/translate/translate-strings/run-extract', ['paths' => 'src', 'output_path' => 'blocker/locales']);
		$this->assertResponseOk();
		$this->assertNull($this->viewVariable('returnCode'));
		$this->assertFlashMessageContains('Failed to create output directory');
		$this->assertSame($before, glob(sys_get_temp_dir() . '/translate_extract_*'));
	}

	public function testOutputFileSymlinkCannotEscapeProject(): void {
		$outside = tempnam(TMP, 'extract_outside_');
		file_put_contents($outside, 'original');
		symlink($outside, $this->projectPath . '/resources/locales/default.pot');
		try {
			$before = glob(sys_get_temp_dir() . '/translate_extract_*');
			$this->post('/admin/translate/translate-strings/run-extract', ['paths' => 'src', 'overwrite' => true]);
			$this->assertResponseOk();
			$this->assertSame(1, $this->viewVariable('returnCode'));
			$this->assertSame('original', file_get_contents($outside));
			$this->assertFlashMessageContains('Output file must not be a symbolic link');
			$this->assertSame($before, glob(sys_get_temp_dir() . '/translate_extract_*'));
		} finally {
			unlink($this->projectPath . '/resources/locales/default.pot');
			unlink($outside);
		}
	}

}
