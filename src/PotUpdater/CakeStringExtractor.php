<?php

namespace Translate\PotUpdater;

use Cake\Command\I18nExtractCommand;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\TestSuite\StubConsoleInput;
use Cake\Console\TestSuite\StubConsoleOutput;
use Cake\Core\App;
use ReflectionClass;

/**
 * String extractor that wraps CakePHP's I18nExtractCommand.
 *
 * This ensures we use the exact same extraction logic as CakePHP core,
 * avoiding any divergence in behavior.
 */
class CakeStringExtractor extends I18nExtractCommand {

	/**
	 * Extracted translations (captured before file write)
	 *
	 * @var array<string, array<string, array<string, array>>>
	 */
	protected array $extractedTranslations = [];

	/**
	 * Base path for calculating relative references
	 */
	protected string $basePath = '';

	/**
	 * Extract strings from given paths
	 *
	 * @param array<string> $paths Paths to scan
	 * @param string $domain Expected domain name
	 * @param string|null $basePath Base path for relative references
	 * @return array<string, array<string, array{
	 *   msgid: string,
	 *   msgid_plural: string|null,
	 *   msgctxt: string|null,
	 *   references: array<string>,
	 *   comments: array<string>
	 * }>>
	 */
	public function extractStrings(array $paths, string $domain = 'default', ?string $basePath = null): array {
		// Define required constants if not already defined
		$firstPath = $paths[0] ?? (getcwd() ?: '.');
		$this->basePath = $basePath ?? dirname($firstPath);
		$this->ensureConstants($this->basePath);
		// Reset state
		$this->_paths = $paths;
		$this->_files = [];
		$this->_translations = [];
		$this->extractedTranslations = [];

		// Search for files
		$this->_searchFiles();

		// Create stub IO to suppress output
		$out = new StubConsoleOutput();
		$err = new StubConsoleOutput();
		$in = new StubConsoleInput([]);
		$io = new ConsoleIo($out, $err, $in);

		// Create stub arguments
		$args = new Arguments([], [], []);

		// Extract tokens (this populates $this->_translations)
		$this->_extractTokens($args, $io);

		// Convert to our format
		return $this->convertTranslations();
	}

	/**
	 * Convert CakePHP's translation format to our format
	 *
	 * @return array<string, array<string, array{
	 *   msgid: string,
	 *   msgid_plural: string|null,
	 *   msgctxt: string|null,
	 *   references: array<string>,
	 *   comments: array<string>
	 * }>>
	 */
	protected function convertTranslations(): array {
		$result = [];

		foreach ($this->_translations as $domain => $messages) {
			$result[$domain] = [];

			foreach ($messages as $msgid => $contexts) {
				foreach ($contexts as $context => $details) {
					// Build key (context + msgid)
					$key = $context !== '' ? "{$context}\x04{$msgid}" : $msgid;

					// Convert references
					$references = [];
					if (!empty($details['references'])) {
						foreach ($details['references'] as $file => $lines) {
							foreach (array_unique($lines) as $line) {
								$references[] = $file . ':' . $line;
							}
						}
					}

					$result[$domain][$key] = [
						'msgid' => $msgid,
						'msgid_plural' => $details['msgid_plural'] ?: null,
						'msgctxt' => $context !== '' ? $context : null,
						'references' => $references,
						'comments' => [],
					];
				}
			}
		}

		return $result;
	}

	/**
	 * Get strings for a specific domain
	 *
	 * @param array<string> $paths Paths to scan
	 * @param string $domain Domain name
	 * @return array<string, array{
	 *   msgid: string,
	 *   msgid_plural: string|null,
	 *   msgctxt: string|null,
	 *   references: array<string>,
	 *   comments: array<string>
	 * }>
	 */
	public function extractStringsForDomain(array $paths, string $domain): array {
		$all = $this->extractStrings($paths, $domain);

		return $all[$domain] ?? [];
	}

	/**
	 * Ensure CakePHP constants are defined
	 *
	 * @param string $basePath Base path to use for ROOT/APP
	 * @return void
	 */
	protected function ensureConstants(string $basePath): void {
		if (!defined('ROOT')) {
			define('ROOT', $basePath);
		}
		if (!defined('APP')) {
			define('APP', $basePath . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR);
		}
		if (!defined('CAKE')) {
			// Point to CakePHP source - find it via autoloader
			$cakePath = dirname((string)(new ReflectionClass(App::class))->getFileName(), 2);
			define('CAKE', $cakePath . DIRECTORY_SEPARATOR);
		}
		if (!defined('CAKE_CORE_INCLUDE_PATH')) {
			define('CAKE_CORE_INCLUDE_PATH', dirname(CAKE));
		}
	}

}
