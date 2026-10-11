<?php

namespace Translate\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Plugin;
use Cake\Utility\Inflector;
use Translate\Filesystem\Dumper;
use Translate\Model\Entity\TranslateProject;

/**
 * @property \Translate\Model\Table\TranslateStringsTable $TranslateStrings
 */
class I18nDumpCommand extends Command {

	protected ?string $defaultTable = 'Translate.TranslateStrings';

	/**
	 * Output directories for every generated catalog.
	 *
	 * @var array<string>
	 */
	protected array $_paths = [];

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
		$this->_paths = [$path];
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
		if ($args->getOption('paths')) {
			$this->_paths = explode(',', (string)$args->getOption('paths'));
		}
		if ($args->getOption('plugin')) {
			$plugin = Inflector::camelize((string)$args->getOption('plugin'));
			if ($this->_paths === []) {
				$this->_paths = [Plugin::path($plugin) . 'resources/locales/'];
			}
		}
		$this->_projectId ??= $this->findProjectId($plugin);
		if ($this->_projectId === null) {
			$io->abort('No project found.');
		}

		/** @var \Translate\Model\Entity\TranslateLocale[] $languages */
		$languages = $this->fetchTable('Translate.TranslateLocales')->getExtractable($this->_projectId)->all()->toArray();
		/** @var \Translate\Model\Entity\TranslateDomain[] $domains */
		$domains = $this->fetchTable('Translate.TranslateDomains')->getActive()->where(['TranslateDomains.translate_project_id' => $this->_projectId])->all()->toArray();
		if (!$domains) {
			$io->abort('No active domains found to dump.');
		}

		$count = 0;
		$failed = false;
		$folders = $this->_paths ?: [null];
		foreach ($domains as $domain) {
			foreach ($languages as $language) {
				/** @var array<\Translate\Model\Entity\TranslateTerm> $translations */
				$translations = $this->fetchTable('Translate.TranslateTerms')->getTranslations($language->id, $domain->id)->toArray();

				if (!$translations) {
					continue;
				}

				$dumper = new Dumper();
				foreach ($folders as $folder) {
					if (!$dumper->dump($translations, $domain->name, $language->locale, $folder)) {
						$failed = true;
						$io->err('Error: ' . $language->locale . '/' . $domain->name);

						continue;
					}
					$count++;
				}
			}

		}

		if ($failed) {
			return static::CODE_ERROR;
		}
		$io->success('Done: ' . $count . ' files');

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
			'Dump translations from database to PO files.',
		])->addOption('paths', [
			'help' => 'Comma separated list of paths to write PO files to.',
		])->addOption('plugin', [
			'help' => 'Dump translations for a specific plugin.',
			'short' => 'p',
		]);

		return $parser;
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
