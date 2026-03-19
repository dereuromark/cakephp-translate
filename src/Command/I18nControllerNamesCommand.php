<?php
declare(strict_types=1);

namespace Translate\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Plugin;
use Cake\Utility\Inflector;
use DirectoryIterator;
use Exception;

/**
 * I18nControllerNames command.
 *
 * Scans controller directories and outputs controller names in singular and plural forms
 * for use in translations.
 */
class I18nControllerNamesCommand extends Command {

	/**
	 * @inheritDoc
	 */
	public static function defaultName(): string {
		return 'i18n controller_names';
	}

	/**
	 * @inheritDoc
	 */
	public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser {
		$parser
			->setDescription('List controller names in singular and plural forms for translation.')
			->addOption('plugin', [
				'short' => 'p',
				'help' => 'Scan only a specific plugin',
			])
			->addOption('app-only', [
				'boolean' => true,
				'help' => 'Scan only the app controllers, not plugins',
			]);

		return $parser;
	}

	/**
	 * @inheritDoc
	 */
	public function execute(Arguments $args, ConsoleIo $io): int {
		/** @var array<string, array<string, array{singular: string, plural: string}>> $controllerNames */
		$controllerNames = [];

		$specificPlugin = $args->getOption('plugin');
		$specificPlugin = is_string($specificPlugin) ? $specificPlugin : null;
		$appOnly = $args->getOption('app-only');

		// Scan app controllers
		if (!$specificPlugin) {
			$appPath = APP . 'Controller' . DIRECTORY_SEPARATOR;
			if (is_dir($appPath)) {
				$io->verbose('Scanning: ' . $appPath);
				$controllerNames['App'] = $this->scanControllerDirectory($appPath);
			}
		}

		// Scan plugin controllers
		if (!$appOnly) {
			$plugins = $specificPlugin ? [$specificPlugin] : Plugin::loaded();

			foreach ($plugins as $plugin) {
				try {
					$pluginPath = Plugin::classPath($plugin) . 'Controller' . DIRECTORY_SEPARATOR;
					if (is_dir($pluginPath)) {
						$io->verbose('Scanning: ' . $pluginPath);
						$names = $this->scanControllerDirectory($pluginPath);
						if ($names) {
							$controllerNames[$plugin] = $names;
						}
					}
				} catch (Exception $e) {
					$io->verbose('Could not scan plugin: ' . $plugin);
				}
			}
		}

		if (!$controllerNames) {
			$io->warning('No controllers found.');

			return static::CODE_SUCCESS;
		}

		$this->outputTable($controllerNames, $io);

		return static::CODE_SUCCESS;
	}

	/**
	 * Scan a controller directory for controller files.
	 *
	 * @param string $path Directory path
	 * @return array<string, array{singular: string, plural: string}>
	 */
	protected function scanControllerDirectory(string $path): array {
		$names = [];

		$iterator = new DirectoryIterator($path);
		foreach ($iterator as $file) {
			if ($file->isDot() || $file->isDir()) {
				continue;
			}

			$filename = $file->getFilename();
			if (!str_ends_with($filename, 'Controller.php')) {
				continue;
			}

			// Skip abstract/base controllers
			if ($filename === 'AppController.php') {
				continue;
			}

			// Extract controller name (e.g., "UsersController.php" -> "Users")
			$controllerName = substr($filename, 0, -14); // Remove "Controller.php"

			$singular = Inflector::singularize($controllerName);
			$plural = Inflector::pluralize($singular);

			// Humanize for display
			$singularHuman = Inflector::humanize(Inflector::underscore($singular));
			$pluralHuman = Inflector::humanize(Inflector::underscore($plural));

			$names[$controllerName] = [
				'singular' => $singularHuman,
				'plural' => $pluralHuman,
			];
		}

		ksort($names);

		return $names;
	}

	/**
	 * Output controller names as a table.
	 *
	 * @param array<string, array<string, array{singular: string, plural: string}>> $controllerNames
	 * @param \Cake\Console\ConsoleIo $io
	 * @return void
	 */
	protected function outputTable(array $controllerNames, ConsoleIo $io): void {
		foreach ($controllerNames as $source => $names) {
			$io->out();
			$io->out('<info>' . $source . '</info>');
			$io->hr();

			$rows = [['Controller', 'Singular', 'Plural']];
			foreach ($names as $controller => $forms) {
				$rows[] = [$controller, $forms['singular'], $forms['plural']];
			}

			$io->helper('Table')->output($rows);
		}

		// Summary
		$totalCount = 0;
		foreach ($controllerNames as $names) {
			$totalCount += count($names);
		}

		$io->out();
		$io->out(sprintf('Total: %d controller(s) found.', $totalCount));
	}

}
