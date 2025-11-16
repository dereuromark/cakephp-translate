<?php

namespace Translate\Command;

use Cake\Command\I18nExtractCommand as CoreI18nExtractCommand;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Core\App;
use Cake\Core\Plugin;
use Cake\Utility\Inflector;
use RuntimeException;
use Translate\Model\Entity\TranslateProject;

/**
 * @property \Translate\Model\Table\TranslateStringsTable $TranslateStrings
 */
class I18nExtractCommand extends CoreI18nExtractCommand {

	/**
	 * @var int|null
	 */
	protected $_projectId;

	/**
	 * @param int $id
	 *
	 * @return void
	 */
	public function setProjectId(int $id): void {
		$this->_projectId = $id;
	}

	/**
	 * @param string $path
	 *
	 * @return void
	 */
	public function setPath(string $path): void {
		$this->_paths[] = $path;
	}

	/**
	 * Execute the command
	 *
	 * @param \Cake\Console\Arguments $args The command arguments.
	 * @param \Cake\Console\ConsoleIo $io The console io
	 * @return int|null The exit code or null for success
	 */
	public function execute(Arguments $args, ConsoleIo $io): ?int {
		$plugin = '';
		if ($args->getOption('exclude')) {
			$this->_exclude = explode(',', (string)$args->getOption('exclude'));
		}
		if ($args->getOption('files')) {
			$this->_files = explode(',', (string)$args->getOption('files'));
		}
		if ($args->getOption('paths')) {
			$this->_paths = explode(',', (string)$args->getOption('paths'));
		}
		if ($args->getOption('plugin')) {
			$plugin = Inflector::camelize((string)$args->getOption('plugin'));
			if ($this->_paths === []) {
				$this->_paths = [Plugin::classPath($plugin), Plugin::templatePath($plugin)];
			}
		} elseif (!$args->getOption('paths')) {
			$this->_getPaths($io);
		}

		$this->_projectId = $this->findProjectId($plugin);

		if ($plugin || $args->hasOption('extract-core')) {
			$this->_extractCore = strtolower((string)$args->getOption('extract-core')) !== 'no';
		} else {
			$response = $io->askChoice(
				'Would you like to extract the messages from the CakePHP core?',
				['y', 'n'],
				'n',
			);
			$this->_extractCore = strtolower($response) === 'y';
		}

		if ($args->hasOption('exclude-plugins') && $this->_isExtractingApp()) {
			$this->_exclude = array_merge($this->_exclude, array_values(App::path('plugins')));
		}

		if ($this->_extractCore) {
			$this->_paths[] = CAKE;
		}

		if ($args->hasOption('merge')) {
			$this->_merge = strtolower((string)$args->getOption('merge')) !== 'no';
		} else {
			$io->out();
			$response = $io->askChoice(
				'Would you like to merge all domain strings into the default.pot file?',
				['y', 'n'],
				'n',
			);
			$this->_merge = strtolower($response) === 'y';
		}

		$this->_markerError = (bool)$args->getOption('marker-error');

		if (!$this->_files) {
			$this->_searchFiles();
		}

		$this->_extract($args, $io);

		return static::CODE_SUCCESS;
	}

	/**
	 * Extract text
	 *
	 * @param \Cake\Console\Arguments $args The Arguments instance
	 * @param \Cake\Console\ConsoleIo $io The io instance
	 * @return void
	 */
	protected function _extract(Arguments $args, ConsoleIo $io): void {
		$io->out();
		$io->out();
		$io->out('Extracting...');
		$io->hr();
		$io->out('Paths:');
		foreach ($this->_paths as $path) {
			$io->out('   ' . $path);
		}
		$this->_extractTokens($args, $io);
		$this->_saveMessages($args, $io);
		$this->_paths = [];
		$this->_files = [];
		$this->_storage = [];
		$this->_translations = [];
		$this->_tokens = [];
		$io->out();
		if ($this->_countMarkerError) {
			$io->err("{$this->_countMarkerError} marker error(s) detected.");
			$io->err(' => Use the --marker-error option to display errors.');
		}

		$io->out('Done.');
	}

	/**
	 * @param \Cake\Console\Arguments $args
	 * @param \Cake\Console\ConsoleIo $io
	 * @return void
	 */
	protected function _saveMessages(Arguments $args, ConsoleIo $io): void {
		$io->out();
		$overwriteAll = false;
		if ($args->getOption('overwrite')) {
			$overwriteAll = true;
		}

		$paths = $this->_paths;
		$paths[] = realpath(APP) . DIRECTORY_SEPARATOR;
		usort($paths, function ($a, $b) {
			return strlen($a) - strlen($b);
		});

		foreach ($this->_translations as $domain => $translations) {
			foreach ($translations as $msgid => $contexts) {
				foreach ($contexts as $context => $details) {
					$files = $details['references'];
					$occurrences = [];
					foreach ($files as $file => $lines) {
						$lines = array_unique($lines);
						$occurrences[] = $file . ':' . implode(';', $lines);
					}
					$occurrences = implode("\n", $occurrences);
					$occurrences = str_replace($paths, '', $occurrences);
					$references = str_replace(DIRECTORY_SEPARATOR, '/', $occurrences);

					$this->_save(
						$domain,
						$msgid,
						$details['msgid_plural'] === false ? null : $details['msgid_plural'],
						$context ?: null,
						$references,
					);
				}
			}
		}
	}

	/**
	 * Save translation record to database.
	 *
	 * @param string $domain Domain name
	 * @param string $singular Singular message id.
	 * @param string|null $plural Plural message id.
	 * @param string|null $context Context.
	 * @param string|null $refs Source code references.
	 *
	 * @throws \RuntimeException
	 *
	 * @return void
	 */
	protected function _save(
		string $domain,
		string $singular,
		?string $plural = null,
		?string $context = null,
		?string $refs = null,
	): void {
		/** @var \Translate\Model\Table\TranslateStringsTable $model */
		$model = $this->fetchTable('Translate.TranslateStrings');

		if (!$this->_projectId) {
			throw new RuntimeException('Project ID needed. Make sure to create a project first.');
		}
		$translationDomain = $model->TranslateDomains->getDomain($this->_projectId, $domain);

		$translation = [
			'name' => $singular,
			'plural' => $plural,
			'context' => $context,
			'references' => $refs,
		];
		$model->import($translation, $translationDomain->id);
	}

	/**
	 * @param string $plugin
	 * @return int|null
	 */
	protected function findProjectId(string $plugin): ?int {
		$project = $this->fetchTable('Translate.TranslateProjects')
			->find()
			->where(['type' => TranslateProject::TYPE_APP, 'default' => true])
			->first();
		if (!$project) {
			$project = $this->fetchTable('Translate.TranslateProjects')
				->newEntity([
					'name' => $plugin ?: 'App',
					'status' => TranslateProject::STATUS_HIDDEN,
				]);
			$this->fetchTable('Translate.TranslateProjects')
				->save($project);
		}

		return $project->id;
	}

}
