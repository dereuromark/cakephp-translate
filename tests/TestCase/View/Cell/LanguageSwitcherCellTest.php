<?php

namespace Translate\Test\TestCase\View\Cell;

use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\Http\Session;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Translate\View\Cell\LanguageSwitcherCell;

class LanguageSwitcherCellTest extends TestCase {

	protected array $fixtures = ['plugin.Translate.TranslateLocales'];

	public static function selections(): array {
		return [
			'public selection' => [null, ['TranslatePublicProject' => ['id' => 2], 'TranslateProject' => ['id' => 1]], null, ['de_DE']],
			'admin selection' => ['Admin', ['TranslatePublicProject' => ['id' => 2], 'TranslateProject' => ['id' => 1]], null, ['en_US']],
			'explicit selection' => [null, ['TranslatePublicProject' => ['id' => 2]], 1, ['en_US']],
			'no public selection' => [null, ['TranslateProject' => ['id' => 1]], null, []],
		];
	}

	#[DataProvider('selections')]
	public function testSelectedProjectLanguages(?string $prefix, array $sessionData, ?int $projectId, array $expected): void {
		$locales = $this->fetchTable('Translate.TranslateLocales');
		$locales->saveOrFail($locales->newEntity(['name' => 'German', 'locale' => 'de_DE', 'iso2' => 'de', 'translate_project_id' => 2, 'active' => true]));
		$locales->saveOrFail($locales->newEntity(['name' => 'French', 'locale' => 'fr_FR', 'iso2' => 'fr', 'translate_project_id' => 2, 'active' => false]));
		$session = new Session();
		$session->write($sessionData);
		$request = (new ServerRequest(['session' => $session]))->withParam('prefix', $prefix);
		$cell = new LanguageSwitcherCell($request, new Response());
		$cell->display($projectId);
		$actual = array_map(fn ($locale) => $locale->locale, $cell->viewBuilder()->getVars()['activeLanguages']);
		$this->assertSame($expected, $actual);
	}

}
