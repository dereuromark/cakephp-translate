<?php

namespace Translate\Command;

use Cake\Command\I18nExtractCommand as CoreI18nExtractCommand;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIoInterface;
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
		$this->paths[] = $path;
	}

	/**
	 * Execute the command
	 *
	 * @return int|null The exit code or null for success
	 */
	public function execute(): ?int {
		$args = $this->args;
		$io = $this->io;
		$plugin = '';
		if ($args->getOption('exclude')) {
			$this->exclude = explode(',', (string)$args->getOption('exclude'));
		}
		if ($args->getOption('files')) {
			$this->files = explode(',', (string)$args->getOption('files'));
		}
		if ($args->getOption('paths')) {
			$this->paths = explode(',', (string)$args->getOption('paths'));
		}
		if ($args->getOption('plugin')) {
			$plugin = Inflector::camelize((string)$args->getOption('plugin'));
			if ($this->paths === []) {
				$this->paths = [Plugin::classPath($plugin), Plugin::templatePath($plugin)];
			}
		} elseif (!$args->getOption('paths')) {
			$this->getPaths();
		}

		$this->_projectId ??= $this->findProjectId($plugin);

		if ($plugin || $args->hasOption('extract-core')) {
			$this->extractCore = strtolower((string)$args->getOption('extract-core')) !== 'no';
		} else {
			$response = $io->askChoice(
				'Would you like to extract the messages from the CakePHP core?',
				['y', 'n'],
				'n',
			);
			$this->extractCore = strtolower($response) === 'y';
		}

		if ($args->hasOption('exclude-plugins') && $this->isExtractingApp()) {
			$this->exclude = array_merge($this->exclude, array_values(App::path('plugins')));
		}

		if ($this->extractCore) {
			$this->paths[] = CAKE;
		}

		if ($args->hasOption('merge')) {
			$this->merge = strtolower((string)$args->getOption('merge')) !== 'no';
		} else {
			$io->out();
			$response = $io->askChoice(
				'Would you like to merge all domain strings into the default.pot file?',
				['y', 'n'],
				'n',
			);
			$this->merge = strtolower($response) === 'y';
		}

		$this->markerError = (bool)$args->getOption('marker-error');

		if (!$this->files) {
			$this->searchFiles();
		}

		$this->_extract($args, $io);

		return static::CODE_SUCCESS;
	}

	/**
	 * Extract text
	 *
	 * @param \Cake\Console\Arguments $args The Arguments instance
	 * @param \Cake\Console\ConsoleIoInterface $io The io instance
	 * @return void
	 */
	protected function _extract(Arguments $args, ConsoleIoInterface $io): void {
		$io->out();
		$io->out();
		$io->out('Extracting...');
		$io->hr();
		$io->out('Paths:');
		foreach ($this->paths as $path) {
			$io->out('   ' . $path);
		}
		$this->extractTokens();
		$this->_saveMessages($args, $io);
		$this->paths = [];
		$this->files = [];
		$this->storage = [];
		$this->translations = [];
		$this->tokens = [];
		$io->out();
		if ($this->countMarkerError) {
			$io->err("{$this->countMarkerError} marker error(s) detected.");
			$io->err(' => Use the --marker-error option to display errors.');
		}

		$io->out('Done.');
	}

	/**
	 * @param \Cake\Console\Arguments $args
	 * @param \Cake\Console\ConsoleIoInterface $io
	 * @return void
	 */
	protected function _saveMessages(Arguments $args, ConsoleIoInterface $io): void {
		$io->out();
		$overwriteAll = false;
		if ($args->getOption('overwrite')) {
			$overwriteAll = true;
		}

		$project = $this->fetchTable('Translate.TranslateProjects')->get($this->_projectId);
		$projectRoot = $project->get('path') ?: ROOT;
		if (!str_starts_with($projectRoot, DS)) {
			$projectRoot = ROOT . DS . $projectRoot;
		}
		$projectRoot = rtrim(realpath($projectRoot) ?: $projectRoot, DS) . DS;

		foreach ($this->translations as $domain => $translations) {
			foreach ($translations as $msgid => $contexts) {
				foreach ($contexts as $context => $details) {
					$files = $details['references'];
					$occurrences = [];
					foreach ($files as $file => $lines) {
						$lines = array_unique($lines);
						$referenceFile = str_starts_with($file, $projectRoot) ? substr($file, strlen($projectRoot)) : $file;
						$occurrences[] = $referenceFile . ':' . implode(';', $lines);
					}
					$occurrences = implode("\n", $occurrences);
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
		/** @var \Translate\Model\Entity\TranslateProject|null $project */
		$project = $this->fetchTable('Translate.TranslateProjects')
			->find()
			->where($plugin !== ''
				? ['type' => TranslateProject::TYPE_PLUGIN, 'name' => $plugin]
				: ['type' => TranslateProject::TYPE_APP, 'default' => true])
			->first();
		if (!$project) {
			/** @var \Translate\Model\Entity\TranslateProject $project */
			$project = $this->fetchTable('Translate.TranslateProjects')
				->newEntity([
					'name' => $plugin ?: 'App',
					'type' => $plugin !== '' ? TranslateProject::TYPE_PLUGIN : TranslateProject::TYPE_APP,
					'default' => $plugin === '',
					'status' => TranslateProject::STATUS_HIDDEN,
				]);
			$this->fetchTable('Translate.TranslateProjects')
				->saveOrFail($project);
		}

		return $project->id;
	}

}
