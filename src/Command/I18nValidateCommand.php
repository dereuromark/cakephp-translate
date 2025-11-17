<?php

namespace Translate\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\App;
use Cake\I18n\Parser\PoFileParser;
use Translate\Filesystem\Folder;

class I18nValidateCommand extends Command {

	/**
	 * Paths to use when looking for strings
	 *
	 * @var list<string>
	 */
	protected array $_paths = [];

	/**
	 * Execute the command
	 *
	 * @param \Cake\Console\Arguments $args The command arguments.
	 * @param \Cake\Console\ConsoleIo $io The console io
	 * @return int|null The exit code or null for success
	 */
	public function execute(Arguments $args, ConsoleIo $io): ?int {
		if ($args->getOption('paths')) {
			$this->_paths = explode(',', (string)$args->getOption('paths'));
		} else {
			$this->_getPaths($io, (string)$args->getOption('plugin') ?: null);
		}

		$exitCode = static::CODE_SUCCESS;
		foreach ($this->_paths as $path) {
			$folderContent = (new Folder($path))->read();
			$locales = $folderContent[0] ?? [];
			foreach ($locales as $locale) {
				$folderContent = (new Folder($path . DS . $locale . DS))->read();
				if (empty($folderContent[1])) {
					continue;
				}

				foreach ($folderContent[1] as $file) {
					$subPath = $locale . DS . $file;
					$io->out($subPath);

					$result = $this->validate($path . $subPath);
					if (!$result) {
						$io->success('=> OK');

						continue;
					}

					$io->warning('=> ' . count($result) . ' issue(s):');
					foreach ($result as $string => $issues) {
						$io->warning(' - `' . $string . '`:');
						foreach ($issues as $issue) {
							$io->warning('   * ' . $issue);
						}
					}

					$exitCode = static::CODE_ERROR;
				}
			}
		}

		return $exitCode;
	}

	/**
	 * Gets the option parser instance and configures it.
	 *
	 * @param \Cake\Console\ConsoleOptionParser $parser The parser to configure
	 * @return \Cake\Console\ConsoleOptionParser
	 */
	public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser {
		$parser->setDescription([
			static::getDescription(),
			'Validates PO files.',
		])->addOption('paths', [
			'help' => 'Comma separated list of paths that are searched for source files.',
		])->addOption('plugin', [
			'help' => 'Extracts tokens only from the plugin specified and '
				. "puts the result in the plugin's `locales` directory.",
			'short' => 'p',
		]);

		return $parser;
	}

	/**
	 * Method to interact with the user and get path selections.
	 *
	 * @param \Cake\Console\ConsoleIo $io The io instance.
	 * @param string|null $plugin
	 * @return void
	 */
	protected function _getPaths(ConsoleIo $io, ?string $plugin): void {
		$defaultPaths = array_merge(
			array_values(App::path('locales', $plugin)),
			['D'], // This is required to break the loop below
		);
		$defaultPathIndex = 0;
		while (true) {
			$currentPaths = $this->_paths !== [] ? $this->_paths : ['None'];
			$message = sprintf(
				"Current paths: %s\nWhat is the path you would like to validate?\n[Q]uit [D]one",
				implode(', ', $currentPaths),
			);
			$response = $io->ask($message, $defaultPaths[$defaultPathIndex] ?? 'D');
			if (strtoupper($response) === 'Q') {
				$io->err('Extract Aborted');
				$this->abort();
			}
			if (strtoupper($response) === 'D' && count($this->_paths)) {
				$io->out();

				return;
			}
			if (strtoupper($response) === 'D') {
				$io->warning('No directories selected. Please choose a directory.');
			} elseif (is_dir($response)) {
				$this->_paths[] = $response;
				$defaultPathIndex++;
			} else {
				$io->err('The directory path you supplied was not found. Please try again.');
			}
			$io->out();
		}
	}

	/**
	 * @param string $path
	 * @return array
	 */
	protected function validate(string $path): array {
		$content = file_get_contents($path);

		$issues = [];

		$catalog = (new PoFileParser())->parse($path);
		foreach ($catalog as $string => $details) {
			$translations = $details['_context'] ?? [];
			foreach ($translations as $context => $translation) {
				if (!is_string($translation)) {
					// Plural, skip for now
					continue;
				}
				if ($this->hasUnescapedQuote($translation)) {
					$issues[$string][] = 'Unescaped quote in translation `' . $translation . '`';
				}
			}
		}

		return $issues;
	}

	/**
	 * @param string $str
	 * @return bool
	 */
	protected function hasUnescapedQuote(string $str): bool {
		$escaped = false;
		for ($i = 0; $i < strlen($str); $i++) {
			if ($str[$i] === '\\') {
				$escaped = !$escaped;
			} elseif ($str[$i] === '"' && !$escaped) {
				return true;
			} else {
				$escaped = false;
			}
		}

		return false;
	}

}
