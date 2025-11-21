<?php

namespace Translate\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\App;
use Cake\Core\Plugin;
use Translate\Filesystem\Folder;
use Translate\Service\PoAnalyzerService;

/**
 * Validate PO/POT files for common issues.
 *
 * Checks for:
 * - Placeholder mismatches ({0}, %s, %d)
 * - Whitespace differences
 * - HTML tag mismatches
 * - Mixed placeholder styles
 */
class I18nValidateCommand extends Command {

	/**
	 * Paths to use when looking for strings
	 *
	 * @var list<string>
	 */
	protected array $_paths = [];

	/**
	 * @inheritDoc
	 */
	public static function defaultName(): string {
		return 'i18n validate';
	}

	/**
	 * Execute the command
	 *
	 * @param \Cake\Console\Arguments $args The command arguments.
	 * @param \Cake\Console\ConsoleIo $io The console io
	 * @return int|null The exit code or null for success
	 */
	public function execute(Arguments $args, ConsoleIo $io): ?int {
		$keyBased = (bool)$args->getOption('key-based');
		$jsonOutput = (bool)$args->getOption('json');
		$summaryOnly = (bool)$args->getOption('summary');

		if ($args->getOption('paths')) {
			$this->_paths = explode(',', (string)$args->getOption('paths'));
		} else {
			$plugin = $args->getOption('plugin');
			if ($plugin) {
				if (!Plugin::isLoaded((string)$plugin)) {
					$io->error("Plugin '{$plugin}' is not loaded.");

					return static::CODE_ERROR;
				}
				$this->_paths = [Plugin::path((string)$plugin) . 'resources' . DS . 'locales' . DS];
			} else {
				$this->_paths = array_values(App::path('locales'));
			}
		}

		$totalIssues = 0;
		$totalFiles = 0;
		$filesWithIssues = 0;
		$allResults = [];

		$analyzer = new PoAnalyzerService();

		foreach ($this->_paths as $basePath) {
			if (!is_dir($basePath)) {
				if (!$jsonOutput) {
					$io->warning("Path not found: {$basePath}");
				}

				continue;
			}

			$files = $this->findTranslationFiles($basePath);

			foreach ($files as $file) {
				$content = file_get_contents($file);
				if ($content === false) {
					if (!$jsonOutput) {
						$io->warning("Could not read file: {$file}");
					}

					continue;
				}

				$result = $analyzer->analyze($content, $keyBased ? true : null);
				$issueCount = count($result['issues']);
				$totalFiles++;

				$relativePath = str_replace($basePath, '', $file);
				$allResults[$relativePath] = $result;

				if ($issueCount > 0) {
					$filesWithIssues++;
					$totalIssues += $issueCount;

					if (!$jsonOutput && !$summaryOnly) {
						$io->out('');
						$io->out("<warning>{$relativePath}</warning> - {$issueCount} issue(s)");

						foreach ($result['issues'] as $msgid => $issues) {
							foreach ($issues as $type => $details) {
								$io->out("  <error>[{$type}]</error> " . $this->truncate((string)$msgid, 60));
								if (!empty($details['message'])) {
									$io->out("    {$details['message']}");
								}
							}
						}
					}
				} elseif (!$jsonOutput && !$summaryOnly) {
					$io->out("<success>{$relativePath}</success> - OK");
				}
			}
		}

		// Output results
		if ($jsonOutput) {
			$output = [
				'paths' => $this->_paths,
				'summary' => [
					'total_files' => $totalFiles,
					'files_with_issues' => $filesWithIssues,
					'total_issues' => $totalIssues,
				],
				'files' => $allResults,
			];
			$io->out((string)json_encode($output, JSON_PRETTY_PRINT));
		} else {
			$io->hr();
			$io->out('');
			$io->out('<info>Summary:</info>');
			$io->out("  Files scanned: {$totalFiles}");
			$io->out("  Files with issues: {$filesWithIssues}");
			$io->out("  Total issues: {$totalIssues}");

			if ($totalIssues === 0) {
				$io->out('');
				$io->success('All translation files are valid!');
			} else {
				$io->out('');
				$io->warning("Found {$totalIssues} issue(s) in {$filesWithIssues} file(s).");
			}
		}

		if ($totalIssues > 0) {
			return static::CODE_ERROR;
		}

		return static::CODE_SUCCESS;
	}

	/**
	 * Gets the option parser instance and configures it.
	 *
	 * @param \Cake\Console\ConsoleOptionParser $parser The parser to configure
	 * @return \Cake\Console\ConsoleOptionParser
	 */
	public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser {
		$parser->setDescription([
			'Validate PO/POT translation files for common issues.',
			'',
			'Checks for:',
			'- Placeholder mismatches ({0}, %s, %d)',
			'- Whitespace differences',
			'- HTML tag mismatches',
			'- Mixed placeholder styles',
		])->addOption('paths', [
			'help' => 'Comma separated list of paths to scan for PO/POT files.',
		])->addOption('plugin', [
			'help' => 'Validate translations for a specific plugin.',
			'short' => 'p',
		])->addOption('key-based', [
			'short' => 'k',
			'help' => 'Treat msgid as translation keys (skip HTML/whitespace checks).',
			'boolean' => true,
			'default' => false,
		])->addOption('json', [
			'help' => 'Output results as JSON.',
			'boolean' => true,
			'default' => false,
		])->addOption('summary', [
			'short' => 's',
			'help' => 'Only show summary, not individual issues.',
			'boolean' => true,
			'default' => false,
		]);

		return $parser;
	}

	/**
	 * Find all PO/POT files in a directory recursively.
	 *
	 * @param string $basePath Base path to search
	 * @return array<string> List of file paths
	 */
	protected function findTranslationFiles(string $basePath): array {
		$files = [];

		// First check for POT files in root
		$potFiles = glob($basePath . '*.pot') ?: [];
		$files = array_merge($files, $potFiles);

		// Then check locale subdirectories for PO files
		$folderContent = (new Folder($basePath))->read();
		$locales = $folderContent[0] ?? [];

		foreach ($locales as $locale) {
			$localePath = $basePath . $locale . DS;
			if (!is_dir($localePath)) {
				continue;
			}

			$poFiles = glob($localePath . '*.po') ?: [];
			$files = array_merge($files, $poFiles);
		}

		sort($files);

		return $files;
	}

	/**
	 * Truncate a string for display.
	 *
	 * @param string $text Text to truncate
	 * @param int $length Maximum length
	 * @return string
	 */
	protected function truncate(string $text, int $length): string {
		$text = str_replace(["\n", "\r"], ' ', $text);
		if (mb_strlen($text) <= $length) {
			return $text;
		}

		return mb_substr($text, 0, $length - 3) . '...';
	}

}
