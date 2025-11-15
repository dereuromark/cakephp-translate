<?php

namespace Translate\Command;

use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Core\Plugin;
use Cake\Utility\Inflector;
use Shim\Command\Command;
use Translate\Filesystem\Dumper;
use Translate\Model\Entity\TranslateProject;

/**
 * @property \Translate\Model\Table\TranslateStringsTable $TranslateStrings
 */
class I18nDumpCommand extends Command {

	protected ?string $defaultTable = 'Translate.TranslateStrings';

	/**
	 * Paths to use when writing. Only takes first one for now.
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
		$this->_projectId = $this->findProjectId($plugin);

		/** @var \Translate\Model\Entity\TranslateLocale[] $languages */
		$languages = $this->fetchTable('Translate.TranslateLocales')->getExtractable($this->_projectId)->all()->toArray();
		/** @var \Translate\Model\Entity\TranslateDomain[] $domains */
		$domains = $this->fetchTable('Translate.TranslateDomains')->getActive()->all()->toArray();
		if (!$domains) {
			$io->abort('No active domains found to dump.');
		}

		$count = 0;
		foreach ($domains as $domain) {
			foreach ($languages as $language) {
				$translations = $this->fetchTable('Translate.TranslateTerms')->getTranslations($language->id, $domain->id)->toArray();

				if (!$translations) {
					continue;
				}

				$dumper = new Dumper();
				$folder = array_shift($this->_paths) ?: null;
				if (!$dumper->dump($translations, $domain->name, $language->locale, $folder)) {
					$io->err('Error: ' . $language->locale . '/' . $domain->name);
				}
			}

			$count++;
		}

		$io->success('Done: ' . $count . ' files');

		return static::CODE_SUCCESS;
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
