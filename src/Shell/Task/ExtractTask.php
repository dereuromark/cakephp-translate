<?php

namespace Translate\Shell\Task;

use Cake\Command\I18nExtractCommand;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\ORM\TableRegistry;
use RuntimeException;

/**
 * //FIXME
 *
 * Extract shell task extension for import into DB.
 * Do not use this file from I18n shell - it is used inside the business logic of Translate directly.
 */
class ExtractTask extends I18nExtractCommand {

	/**
	 * @var \Translate\Model\Table\TranslateStringsTable
	 */
	protected $_model;

	/**
	 * @var int
	 */
	protected $_projectId;

	/**
	 * @param int $id
	 *
	 * @return void
	 */
	public function setProjectId($id) {
		$this->_projectId = $id;
	}

	/**
	 * @param string $path
	 *
	 * @return void
	 */
	public function setPath($path) {
		$this->_paths[] = $path;
	}

	/**
	 * @param \Cake\Console\Arguments $args
	 * @param \Cake\Console\ConsoleIo $io
	 *
	 * @return void
	 */
	protected function _extract(Arguments $args, ConsoleIo $io): void {
		$this->_extractTokens($args, $io);
		$this->_write();
		$this->_paths = $this->_files = $this->_storage = [];
		$this->_translations = $this->_tokens = [];
	}

	/**
	 * Write to DB
	 *
	 * @return void
	 */
	protected function _write() {
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
	protected function _save($domain, $singular, $plural = null, $context = null, $refs = null) {
		$model = $this->_model();

		if (!$this->_projectId) {
			throw new RuntimeException('Project ID needed');
		}
		$translationGroup = $model->TranslateDomains->getDomain($this->_projectId, $domain);

		$translation = [
			'name' => $singular,
			'plural' => $plural,
			'context' => $context,
			'references' => $refs,
		];
		$model->import($translation, $translationGroup->id);
	}

	/**
	 * @return \Translate\Model\Table\TranslateStringsTable
	 */
	protected function _model() {
		if ($this->_model !== null) {
			return $this->_model;
		}

		/** @var \Translate\Model\Table\TranslateStringsTable $model */
		$model = TableRegistry::getTableLocator()->get('Translate.TranslateStrings');

		return $this->_model = $model;
	}

}
