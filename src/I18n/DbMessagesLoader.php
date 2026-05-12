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

		$localeChain = $this->_resolveLocaleFallbackChain($this->locale);
		if ($localeChain === []) {
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
		$query->select([
			'TranslateStrings.name',
			'TranslateStrings.plural',
			'TranslateStrings.context',
			'TranslateLocales.locale',
		]);

		$results = $query
			->where(['TranslateDomains.translate_project_id' => $translateProjectId])
			->where(['TranslateLocales.translate_project_id' => $translateProjectId])
			->where(['TranslateDomains.name' => $this->domain])
			->where(['TranslateLocales.locale IN' => $localeChain])
			->where(['TranslateStrings.active' => true])
			->enableHydration(false)
			->all();

		return new Package($this->formatter, null, $this->_messages($results, $localeChain));
	}

	/**
	 * Build the locale lookup chain. A request for `de_AT` falls back to
	 * `de` (and finally to the source string via Cake's translator)
	 * exactly the way `MessagesFileLoader` walks `.po` files. Locales
	 * without a region (`de`, `en`) chain to themselves only.
	 *
	 * @param string $locale Requested locale (e.g. `de_AT`, `fr_CA`, `en`).
	 *
	 * @return list<string> Locales in priority order — most specific first.
	 */
	protected function _resolveLocaleFallbackChain(string $locale): array {
		if ($locale === '') {
			return [];
		}

		$chain = [$locale];
		$sepPos = strpos($locale, '_');
		if ($sepPos !== false) {
			$parent = substr($locale, 0, $sepPos);
			if ($parent !== '' && $parent !== $locale) {
				$chain[] = $parent;
			}
		}

		return $chain;
	}

	/**
	 * Converts the DB resultset to a messages array, honoring the
	 * locale fallback chain (most-specific locale wins).
	 *
	 * The query may return rows from multiple locales (`de_AT` + `de`
	 * when the caller asked for `de_AT`). Rows are partitioned by
	 * locale and the chain is walked in priority order: the first
	 * locale to provide an entry for a given (singular, context) pair
	 * wins, subsequent locales fill remaining gaps.
	 *
	 * @param \Cake\Datasource\ResultSetInterface $results ResultSet instance.
	 * @param list<string> $localeChain Locales in priority order.
	 *
	 * @return array
	 */
	protected function _messages(ResultSetInterface $results, array $localeChain): array {
		if (!$results->count()) {
			return [];
		}

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

		/** @var array<string, list<array<string, mixed>>> $byLocale */
		$byLocale = [];
		foreach ($results as $row) {
			$locale = $row['translate_locale']['locale'] ?? '';
			if (!is_string($locale) || $locale === '') {
				continue;
			}
			$byLocale[$locale][] = $row;
		}

		$messages = [];
		foreach ($localeChain as $locale) {
			if (!isset($byLocale[$locale])) {
				continue;
			}
			foreach ($byLocale[$locale] as $row) {
				$singular = $row['translate_string']['name'];
				$context = (string)($row['translate_string']['context'] ?? '');
				$translation = $row['content'];

				// First (most-specific) locale to provide this key wins.
				if (!isset($messages[$singular]['_context'][$context])) {
					$messages[$singular]['_context'][$context] = $translation;
				}

				if ($row['translate_string']['plural'] === null) {
					continue;
				}

				$pluralKey = $row['translate_string']['plural'];
				if (isset($messages[$pluralKey]['_context'][$context])) {
					continue;
				}

				$plurals = [$translation];
				for ($i = 1; $i <= $pluralForms; $i++) {
					$plurals[] = $row['plural_' . ($i + 1)];
				}
				$messages[$pluralKey]['_context'][$context] = $plurals;
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
