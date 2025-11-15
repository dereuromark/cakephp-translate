<?php

namespace Translate\I18n;

use Cake\Datasource\RepositoryInterface;
use Cake\Datasource\ResultSetInterface;
use Cake\I18n\Package;
use Cake\ORM\Locator\LocatorAwareTrait;
use Translate\Model\Entity\TranslateProject;

/**
 * DbMessages loader.
 *
 * Returns translation messages stored in database.
 */
class DbMessagesLoader {

	use LocatorAwareTrait;

	/**
	 * The domain name.
	 *
	 * @var string
	 */
	protected string $domain;

	/**
	 * The locale to load messages for.
	 *
	 * @var string
	 */
	protected string $locale;

	/**
	 * The model name to use for loading messages or model instance.
	 *
	 * @var \Cake\Datasource\RepositoryInterface|string
	 */
	protected string|RepositoryInterface $model;

	/**
	 * Formatting used for messages.
	 *
	 * @var string
	 */
	protected string $formatter;

	/**
	 * Constructor.
	 *
	 * @param string $domain Domain name.
	 * @param string $locale Locale string.
	 * @param \Cake\Datasource\RepositoryInterface|string|null $model Model name or instance.
	 *   Defaults to 'I18nMessages'.
	 * @param string $formatter Formatter name. Defaults to 'default' (ICU formatter).
	 */
	public function __construct(
		string $domain,
		string $locale,
		string|RepositoryInterface|null $model = null,
		string $formatter = 'default',
	) {
		$this->domain = $domain;
		$this->locale = $locale;
		$this->model = $model ?: 'Translate.TranslateTerms';
		$this->formatter = $formatter;
	}

	/**
	 * Fetches the translation messages from db and returns package with those
	 * messages.
	 *
	 * @throws \RuntimeException If model could not be loaded.
	 * @return \Cake\I18n\Package
	 */
	public function __invoke(): Package {
		$model = $this->fetchTable('Translate.TranslateTerms');

		$translateProject = $this->fetchTable('Translate.TranslateProjects')
			->find()
			->where(['type' => TranslateProject::TYPE_APP, 'default' => true])
			->firstOrFail();
		$translateProjectId = $translateProject->id;
		$query = $model->find();

		// Get list of fields without primaryKey, domain, locale.
		/** @var \Cake\Database\Schema\TableSchema $schema */
		$schema = $model->getSchema();
		$fields = $schema->columns();
		$fields = array_flip(array_diff(
			$fields,
			$schema->getPrimaryKey(),
		));
		unset($fields['domain'], $fields['locale']);
		$query->select(array_flip($fields));

		$query->contain(['TranslateStrings' => 'TranslateDomains', 'TranslateLocales']);
		$query->select(['TranslateStrings.name', 'TranslateStrings.plural', 'TranslateStrings.context']);

		// Get list of fields without primaryKey, domain, locale.
		$fields = $model->getSchema()->columns();
		$fields = array_flip(array_diff(
			$fields,
			$model->getSchema()->getPrimaryKey(),
		));
		unset($fields['domain'], $fields['locale']);
		$query->select(array_flip($fields));

		$results = $query
			->where(['TranslateDomains.translate_project_id' => $translateProjectId])
			->where(['TranslateLocales.translate_project_id' => $translateProjectId])
			->where(['TranslateDomains.name' => $this->domain, 'TranslateLocales.locale' => $this->locale])
			->where(['TranslateStrings.active' => true])
			->enableHydration(false)
			->all();

		return new Package($this->formatter, null, $this->_messages($results));
	}

	/**
	 * Converts DB resultset to messages array.
	 *
	 * @param \Cake\Datasource\ResultSetInterface $results ResultSet instance.
	 * @return array
	 */
	protected function _messages(ResultSetInterface $results): array {
		if (!$results->count()) {
			return [];
		}

		$messages = [];
		$pluralForms = 0;
		$item = $results->first();
		// There are max 6 plural forms possible but most people won't need
		// that so will only have the required number of value_{n} fields in db.
		for ($i = 7; $i >= 2; $i--) {
			if (isset($item['plural_' . $i])) {
				$pluralForms = $i - 1;

				break;
			}
		}

		foreach ($results as $item) {
			$singular = $item['translate_string']['name'];
			$context = $item['translate_string']['context'];
			$translation = $item['content'];
			if ($context) {
				$messages[$singular]['_context'][$context] = $translation;
			} else {
				$messages[$singular]['_context'][''] = $translation;
			}

			if ($item['translate_string']['plural'] === null) {
				continue;
			}

			$key = $item['translate_string']['plural'];
			$plurals = [
				$translation,
			];
			for ($i = 1; $i <= $pluralForms; $i++) {
				$plurals[] = $item['plural_' . ($i + 1)];
			}

			if ($context) {
				$messages[$key]['_context'][$context] = $plurals;
			} else {
				$messages[$key]['_context'][''] = $plurals;
			}
		}

		return $messages;
	}

	/**
	 * Get model instance
	 *
	 * @return \Cake\Datasource\RepositoryInterface
	 */
	protected function _getModel(): RepositoryInterface {
		if (is_string($this->model)) {
			/** @var \Translate\Model\Table\TranslateTermsTable $model */
			$model = $this->getTableLocator()->get($this->model);
			$this->model = $model;
		}

		/** @var \Translate\Model\Table\TranslateTermsTable */
		return $this->model;
	}

}
