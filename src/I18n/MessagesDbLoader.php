<?php

namespace Translate\I18n;

use Aura\Intl\Package;
use Cake\Datasource\ResultSetInterface;
use Cake\ORM\TableRegistry;
use RuntimeException;

/**
 * Returns translation messages stored in database.
 */
class MessagesDbLoader {

	/**
	 * The domain name.
	 *
	 * @var string
	 */
	protected $_domain;

	/**
	 * The locale to load messages for.
	 *
	 * @var string
	 */
	protected $_locale;

	/**
	 * The model name to use for loading messages or model instance.
	 *
	 * @var string|\Translate\Model\Table\TranslateTermsTable
	 */
	protected $_model = 'Translate.TranslateTerms';

	/**
	 * Formatting used for messages.
	 *
	 * @var string
	 */
	protected $_formatter;

	/**
	 * Constructor.
	 *
	 * @param string $domain Domain name.
	 * @param string $locale Locale string.
	 * @param string $formatter Formatter name. Defaults to 'default' (ICU formatter).
	 */
	public function __construct(
		$domain,
		$locale,
		$formatter = 'default'
	) {
		$this->_domain = $domain;
		$this->_locale = $locale;
		$this->_formatter = $formatter;
	}

	/**
	 * Fetches the translation messages from db and returns package with those
	 * messages.
	 *
	 * @throws \RuntimeException If model could not be loaded.
	 *
	 * @return \Aura\Intl\Package
	 */
	public function __invoke() {
		$model = $this->_model;
		if (is_string($model)) {
			$model = TableRegistry::getTableLocator()->get($this->_model);
			if (!$model) {
				throw new RuntimeException(
					sprintf('Unable to load model "%s".', $this->_model)
				);
			}
			$this->_model = $model;
		}

		$translateProject = $this->_model->TranslateLanguages->TranslateProjects->find()->where(['default' => true])->firstOrFail();
		$translateProjectId = $translateProject->id;

		$query = $model->find();

		// Get list of fields without primaryKey, domain, locale.
		$fields = $model->getSchema()->columns();
		$fields = array_flip(array_diff(
			$fields,
			$model->getSchema()->primaryKey()
		));
		unset($fields['domain'], $fields['locale']);
		$query->select(array_flip($fields));

		$query->contain(['TranslateStrings' => 'TranslateDomains', 'TranslateLanguages']);
		$query->select(['TranslateStrings.name', 'TranslateStrings.plural', 'TranslateStrings.context']);

		$results = $query
			->where(['TranslateDomains.translate_project_id' => $translateProjectId])
			->where(['TranslateLanguages.translate_project_id' => $translateProjectId])
			->where(['TranslateDomains.name' => $this->_domain, 'TranslateLanguages.locale' => $this->_locale])
			->where(['TranslateStrings.active' => true])
			->enableHydration(false)
			->all();

		return new Package($this->_formatter, null, $this->_messages($results));
	}

	/**
	 * Converts DB resultset to messages array.
	 *
	 * @param \Cake\Datasource\ResultSetInterface $results ResultSet instance.
	 *
	 * @return array
	 */
	protected function _messages(ResultSetInterface $results) {
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

}
