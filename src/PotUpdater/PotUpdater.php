<?php

namespace Translate\PotUpdater;

/**
 * POT Updater - Main orchestrator for checking and updating POT files.
 *
 * This is a standalone utility that can verify and update POT files for CakePHP plugins,
 * runnable directly from a plugin's root directory.
 */
class PotUpdater {

	/**
	 * Plugin/project root path
	 */
	protected string $pluginPath;

	/**
	 * Configuration options
	 *
	 * @var array<string, mixed>
	 */
	protected array $options = [
		'dryRun' => true,
		'verbose' => false,
		'quiet' => false,
		'failOnDiff' => false,
		'ignoreReferences' => false,
		'paths' => ['src', 'templates'],
		'outputPath' => 'resources/locales',
		'domain' => null,
		'extensions' => ['php', 'ctp'],
	];

	/**
	 * Output messages
	 *
	 * @var array<string>
	 */
	protected array $output = [];

	/**
	 * Constructor
	 *
	 * @param string $pluginPath Path to plugin root directory
	 */
	public function __construct(string $pluginPath) {
		$this->pluginPath = rtrim($pluginPath, DIRECTORY_SEPARATOR);
	}

	/**
	 * Main entry point for CLI
	 *
	 * @param array<string> $argv Command line arguments
	 * @return int Exit code (0 = success, 1 = differences found, 2 = error)
	 */
	public function run(array $argv): int {
		$this->parseArguments($argv);

		$domain = $this->detectDomain();
		if ($domain === null) {
			$this->error('Could not detect domain name. Use --domain=name to specify.');

			return 2;
		}

		$this->info("POT Updater - " . ($this->options['dryRun'] ? 'Checking' : 'Updating') . " plugin");
		$this->info(str_repeat('=', 50));
		$this->info('');
		$this->info('Plugin path: ' . $this->pluginPath);
		$this->info('Domain: ' . $domain);
		$this->info('');

		// Extract strings using CakePHP's extraction logic (wraps I18nExtractCommand)
		$this->info('Scanning paths:');
		$extractor = new CakeStringExtractor();

		$fullPaths = [];
		foreach ($this->options['paths'] as $path) {
			$fullPath = $this->pluginPath . DIRECTORY_SEPARATOR . $path;
			if (is_dir($fullPath)) {
				$this->info('  - ' . $path . '/');
				$fullPaths[] = $fullPath;
			}
		}

		$allStrings = $extractor->extractStrings($fullPaths, $domain, $this->pluginPath);
		$currentStrings = $allStrings[$domain] ?? [];
		$this->info('');
		$this->info('Extracted ' . count($currentStrings) . ' strings');

		// Parse existing POT file
		$potPath = $this->pluginPath . DIRECTORY_SEPARATOR . $this->options['outputPath'] . DIRECTORY_SEPARATOR . $domain . '.pot';
		$parser = new PotParser();
		$existingStrings = $parser->parse($potPath);

		$this->info('Existing POT: ' . (file_exists($potPath) ? count($existingStrings) . ' strings' : '(not found)'));
		$this->info('');

		// Compare
		$comparator = new PotComparator();
		$diff = $comparator->compare($existingStrings, $currentStrings);
		$summary = $comparator->getSummary($diff);

		// Display results
		$this->displayDiff($diff, $summary);

		// Update if requested
		if (!$this->options['dryRun']) {
			$writer = new PotWriter();
			$success = $writer->write($potPath, $currentStrings, [
				'project' => $domain,
			]);

			if ($success) {
				$this->info('');
				$this->success('POT file updated: ' . $potPath);
			} else {
				$this->error('Failed to write POT file: ' . $potPath);

				return 2;
			}
		}

		// Determine exit code
		$hasDiff = $comparator->hasDifferences($diff, $this->options['ignoreReferences']);

		if ($hasDiff) {
			if ($this->options['dryRun']) {
				$this->info('');
				$this->info('Run with --update to regenerate POT file.');
			}

			if ($this->options['failOnDiff']) {
				return 1;
			}
		} else {
			$this->info('');
			$this->success('POT file is up to date.');
		}

		return 0;
	}

	/**
	 * Parse command line arguments
	 *
	 * @param array<string> $argv Arguments
	 * @return void
	 */
	protected function parseArguments(array $argv): void {
		foreach ($argv as $arg) {
			if ($arg === '--update') {
				$this->options['dryRun'] = false;
			} elseif ($arg === '--dry-run') {
				$this->options['dryRun'] = true;
			} elseif ($arg === '--verbose' || $arg === '-v') {
				$this->options['verbose'] = true;
				$this->options['quiet'] = false;
			} elseif ($arg === '--quiet' || $arg === '-q') {
				$this->options['quiet'] = true;
				$this->options['verbose'] = false;
			} elseif ($arg === '--fail-on-diff') {
				$this->options['failOnDiff'] = true;
			} elseif ($arg === '--ignore-references') {
				$this->options['ignoreReferences'] = true;
			} elseif (str_starts_with($arg, '--domain=')) {
				$this->options['domain'] = substr($arg, 9);
			} elseif (str_starts_with($arg, '--path=')) {
				$paths = substr($arg, 7);
				$this->options['paths'] = explode(',', $paths);
			} elseif (str_starts_with($arg, '--output=')) {
				$this->options['outputPath'] = substr($arg, 9);
			}
		}
	}

	/**
	 * Detect domain name from composer.json or directory
	 *
	 * @return string|null
	 */
	protected function detectDomain(): ?string {
		if ($this->options['domain'] !== null) {
			return $this->options['domain'];
		}

		$composerPath = $this->pluginPath . DIRECTORY_SEPARATOR . 'composer.json';
		if (file_exists($composerPath)) {
			$composer = json_decode((string)file_get_contents($composerPath), true);
			if (isset($composer['name'])) {
				// Extract plugin name from "vendor/plugin-name"
				$parts = explode('/', (string)$composer['name']);
				$name = end($parts);
				// Convert "cakephp-foo" to "foo" or just use as-is
				if (str_starts_with($name, 'cakephp-')) {
					$name = substr($name, 8);
				}

				// Convert to underscore case for domain
				return $this->toUnderscore($name);
			}
		}

		// Fall back to directory name
		$dirName = basename($this->pluginPath);

		return $this->toUnderscore($dirName);
	}

	/**
	 * Convert string to underscore case
	 *
	 * @param string $string Input string
	 * @return string
	 */
	protected function toUnderscore(string $string): string {
		// Replace dashes with underscores
		$string = str_replace('-', '_', $string);

		// Convert CamelCase to snake_case
		$string = preg_replace('/([a-z])([A-Z])/', '$1_$2', $string);

		return strtolower($string ?? '');
	}

	/**
	 * Display diff results
	 *
	 * @param array{
	 *   added: array<string, array>,
	 *   removed: array<string, array>,
	 *   changed: array<string, array>,
	 *   unchanged: array<string, array>
	 * } $diff Comparison result
	 * @param array{
	 *   added: int,
	 *   removed: int,
	 *   changed: int,
	 *   unchanged: int,
	 *   total_existing: int,
	 *   total_current: int
	 * } $summary Summary statistics
	 * @return void
	 */
	protected function displayDiff(array $diff, array $summary): void {
		$this->info('Results:');
		$this->info('  Existing strings: ' . $summary['total_existing']);
		$this->info('  Current strings:  ' . $summary['total_current']);
		$this->info('');

		if ($summary['added'] > 0) {
			$this->info('  + ' . $summary['added'] . ' new string(s):');
			if ($this->options['verbose']) {
				foreach (array_slice($diff['added'], 0, 20) as $data) {
					$this->info('    - "' . $this->truncate($data['msgid'], 60) . '"');
				}
				if ($summary['added'] > 20) {
					$this->info('    ... and ' . ($summary['added'] - 20) . ' more');
				}
			}
		}

		if ($summary['removed'] > 0) {
			$this->info('  - ' . $summary['removed'] . ' removed string(s):');
			if ($this->options['verbose']) {
				foreach (array_slice($diff['removed'], 0, 20) as $data) {
					$this->info('    - "' . $this->truncate($data['msgid'], 60) . '"');
				}
				if ($summary['removed'] > 20) {
					$this->info('    ... and ' . ($summary['removed'] - 20) . ' more');
				}
			}
		}

		if ($summary['changed'] > 0 && !$this->options['ignoreReferences']) {
			$this->info('  ~ ' . $summary['changed'] . ' string(s) with updated references');
		}

		$this->info('');

		$hasDiff = $summary['added'] > 0 || $summary['removed'] > 0 ||
			($summary['changed'] > 0 && !$this->options['ignoreReferences']);

		if ($hasDiff) {
			$this->warn('Status: OUT OF DATE');
		} else {
			$this->success('Status: UP TO DATE');
		}
	}

	/**
	 * Truncate a string
	 *
	 * @param string $string Input string
	 * @param int $length Max length
	 * @return string
	 */
	protected function truncate(string $string, int $length): string {
		// Replace newlines for display
		$string = str_replace(["\n", "\r"], ['\\n', ''], $string);

		if (strlen($string) <= $length) {
			return $string;
		}

		return substr($string, 0, $length - 3) . '...';
	}

	/**
	 * Output info message
	 *
	 * @param string $message Message
	 * @return void
	 */
	protected function info(string $message): void {
		if (!$this->options['quiet']) {
			$this->output[] = $message;
			echo $message . "\n";
		}
	}

	/**
	 * Output warning message
	 *
	 * @param string $message Message
	 * @return void
	 */
	protected function warn(string $message): void {
		$this->output[] = '[WARN] ' . $message;
		echo "\033[33m" . $message . "\033[0m\n";
	}

	/**
	 * Output success message
	 *
	 * @param string $message Message
	 * @return void
	 */
	protected function success(string $message): void {
		$this->output[] = '[OK] ' . $message;
		echo "\033[32m" . $message . "\033[0m\n";
	}

	/**
	 * Output error message
	 *
	 * @param string $message Message
	 * @return void
	 */
	protected function error(string $message): void {
		$this->output[] = '[ERROR] ' . $message;
		echo "\033[31m" . $message . "\033[0m\n";
	}

	/**
	 * Get output messages (for testing)
	 *
	 * @return array<string>
	 */
	public function getOutput(): array {
		return $this->output;
	}

	/**
	 * Set options programmatically (for testing)
	 *
	 * @param array<string, mixed> $options Options to set
	 * @return $this
	 */
	public function setOptions(array $options): self {
		$this->options = array_merge($this->options, $options);

		return $this;
	}

	/**
	 * Get current options
	 *
	 * @return array<string, mixed>
	 */
	public function getOptions(): array {
		return $this->options;
	}

}
