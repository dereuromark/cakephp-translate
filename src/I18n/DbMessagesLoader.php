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
	 * Optional project id to load translations from. When null, the loader
	 * resolves to the default TYPE_APP project (backwards-compatible default).
	 *
	 * @var int|null
	 */
	protected ?int $projectId;

	/**
	 * Constructor.
	 *
	 * @param string $domain Domain name.
	 * @param string $locale Locale string.
	 * @param \Cake\Datasource\RepositoryInterface|string|null $model Model name or instance.
	 *   Defaults to 'Translate.TranslateTerms'.
	 * @param string $formatter Formatter name. Defaults to 'default' (ICU formatter).
	 * @param int|null $projectId Optional project id. When null, the default
	 *   TYPE_APP project is used. Use this in multi-project setups to load
	 *   translations from a non-default project.
	 */
	public function __construct(
		string $domain,
		string $locale,
		string|RepositoryInterface|null $model = null,
		string $formatter = 'default',
		?int $projectId = null,
	) {
		$this->domain = $domain;
		$this->locale = $locale;
		$this->model = $model ?: 'Translate.TranslateTerms';
		$this->formatter = $formatter;
		$this->projectId = $projectId;
	}

	/**
	 * Fetches the translation messages from db and returns package with those
	 * messages.
	 *
	 * @return \Cake\I18n\Package
	 */
	public function __invoke(): Package {
		/** @var \Cake\ORM\Table $model */
		$model = $this->_getModel();

		$translateProjectId = $this->projectId ?? $this->_resolveDefaultProjectId();
		if ($translateProjectId === null) {
			return new Package($this->formatter);
		}

		$query = $model->find();

		// Get list of fields without primaryKey, domain, locale.
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

	/**
	 * Resolve the default TYPE_APP project id when no explicit project was
	 * passed to the constructor. Mirrors the pre-multi-project behavior.
	 *
	 * @return int|null
	 */
	protected function _resolveDefaultProjectId(): ?int {
		$translateProject = $this->fetchTable('Translate.TranslateProjects')
			->find()
			->where(['type' => TranslateProject::TYPE_APP, 'default' => true])
			->first();

		return $translateProject ? (int)$translateProject->id : null;
	}

}
