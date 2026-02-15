<?php
declare(strict_types=1);

namespace Translate\Controller\Admin;

use Cake\Database\Connection;
use Cake\Datasource\ConnectionManager;
use Cake\Http\Exception\NotFoundException;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\Utility\Inflector;
use Translate\Controller\TranslateAppController;
use Translate\Service\I18nTranslatorService;

/**
 * I18nEntries Controller
 *
 * Provides CRUD operations for TranslateBehavior translation entries.
 * Supports both naming conventions:
 * - EAV strategy: *_i18n tables (locale, model, foreign_key, field, content)
 * - ShadowTable strategy: *_translations tables (id, locale, field columns)
 *
 * @property \Translate\Controller\Component\TranslationComponent $Translation
 */
class I18nEntriesController extends TranslateAppController {

	use LocatorAwareTrait;

	/**
	 * Supported table suffixes for translation tables
	 */
	protected const TABLE_SUFFIXES = ['_i18n', '_translations'];

	/**
	 * @var string|null
	 */
	protected ?string $defaultTable = null;

	/**
	 * @var array<string, mixed>
	 */
	protected array $paginate = [
		'limit' => 50,
	];

	/**
	 * Index - list all translation tables with entry counts
	 *
	 * @return void
	 */
	public function index(): void {
		$connection = $this->getConnection();
		$schemaCollection = $connection->getSchemaCollection();
		$allTables = $schemaCollection->listTables();

		$translationTables = $this->getTranslationTablesInfo($allTables, $connection);
		$locales = $this->getAvailableLocales($translationTables, $connection);

		$this->set(compact('translationTables', 'locales'));
	}

	/**
	 * Entries - list entries for a specific translation table
	 *
	 * @param string $tableName Translation table name (e.g., 'articles_i18n' or 'articles_translations')
	 * @return \Cake\Http\Response|null
	 */
	public function entries(string $tableName) {
		if (!$this->validateTranslationTableName($tableName)) {
			$this->Flash->error(__d('translate', 'Invalid translation table name.'));

			return $this->redirect(['action' => 'index']);
		}

		$connection = $this->getConnection();
		$schemaCollection = $connection->getSchemaCollection();

		if (!in_array($tableName, $schemaCollection->listTables(), true)) {
			$this->Flash->error(__d('translate', 'Translation table not found.'));

			return $this->redirect(['action' => 'index']);
		}

		$baseTableName = $this->getBaseTableName($tableName);
		$schema = $schemaCollection->describe($tableName);
		$strategy = $this->detectTranslationStrategy($schema);
		$translatedFields = $this->getTranslatedFieldsFromSchema($schema, $strategy);
		$locales = $this->getLocalesForTable($connection, $tableName);
		$foreignKeyColumn = $this->getForeignKeyColumn($schema);

		// Build query
		$table = $this->getTranslationTable($tableName);
		$query = $table->find();

		// Apply filters
		$locale = $this->request->getQuery('locale');
		if ($locale) {
			$query->where(['locale' => $locale]);
		}

		$field = $this->request->getQuery('field');
		if ($field && $strategy === 'eav') {
			$query->where(['field' => $field]);
		}

		$autoFilter = $this->request->getQuery('auto');
		if ($autoFilter !== null && $autoFilter !== '') {
			if ($schema->hasColumn('auto')) {
				$query->where(['auto' => (bool)$autoFilter]);
			}
		}

		$search = $this->request->getQuery('search');
		if ($search) {
			if ($strategy === 'eav') {
				$query->where(['content LIKE' => '%' . $search . '%']);
			} else {
				$conditions = ['OR' => []];
				foreach ($translatedFields as $f) {
					$conditions['OR'][$f . ' LIKE'] = '%' . $search . '%';
				}
				if (!empty($conditions['OR'])) {
					$query->where($conditions);
				}
			}
		}

		$query->orderBy(['id' => 'DESC']);
		$entries = $this->paginate($query);

		$hasAutoField = $schema->hasColumn('auto');

		$this->set(compact(
			'tableName',
			'baseTableName',
			'entries',
			'strategy',
			'translatedFields',
			'locales',
			'hasAutoField',
			'foreignKeyColumn',
		));

		return null;
	}

	/**
	 * View a single translation entry
	 *
	 * @param string $tableName Translation table name
	 * @param int $id Entry ID
	 * @return \Cake\Http\Response|null
	 */
	public function view(string $tableName, int $id) {
		if (!$this->validateTranslationTableName($tableName)) {
			throw new NotFoundException(__d('translate', 'Invalid translation table.'));
		}

		$table = $this->getTranslationTable($tableName);
		$entry = $table->get($id);

		$baseTableName = $this->getBaseTableName($tableName);
		$connection = $this->getConnection();
		$schema = $connection->getSchemaCollection()->describe($tableName);
		$strategy = $this->detectTranslationStrategy($schema);
		$translatedFields = $this->getTranslatedFieldsFromSchema($schema, $strategy);
		$hasAutoField = $schema->hasColumn('auto');
		$foreignKeyColumn = $this->getForeignKeyColumn($schema);

		// Get base record info if possible
		$baseRecord = null;
		$foreignKey = $entry->get($foreignKeyColumn);
		if ($foreignKey) {
			try {
				$baseTable = $this->fetchTable(Inflector::camelize($baseTableName));
				$baseRecord = $baseTable->get($foreignKey);
			} catch (\Exception $e) {
				// Base record not found or table doesn't exist
			}
		}

		// Get glossary suggestions
		$glossarySuggestions = [];
		/** @var string|null $content */
		$content = $entry->get('content');
		/** @var string|null $locale */
		$locale = $entry->get('locale');
		if ($strategy === 'eav' && $content && $locale) {
			$glossarySuggestions = $this->getGlossarySuggestions($content, $locale);
		}

		$this->set(compact(
			'tableName',
			'baseTableName',
			'entry',
			'strategy',
			'translatedFields',
			'hasAutoField',
			'baseRecord',
			'glossarySuggestions',
			'foreignKeyColumn',
		));

		return null;
	}

	/**
	 * Edit a translation entry
	 *
	 * @param string $tableName Translation table name
	 * @param int $id Entry ID
	 * @return \Cake\Http\Response|null
	 */
	public function edit(string $tableName, int $id) {
		if (!$this->validateTranslationTableName($tableName)) {
			throw new NotFoundException(__d('translate', 'Invalid translation table.'));
		}

		$table = $this->getTranslationTable($tableName);
		$entry = $table->get($id);

		$connection = $this->getConnection();
		$schema = $connection->getSchemaCollection()->describe($tableName);
		$strategy = $this->detectTranslationStrategy($schema);
		$translatedFields = $this->getTranslatedFieldsFromSchema($schema, $strategy);
		$hasAutoField = $schema->hasColumn('auto');
		$baseTableName = $this->getBaseTableName($tableName);
		$foreignKeyColumn = $this->getForeignKeyColumn($schema);

		if ($this->request->is(['patch', 'post', 'put'])) {
			$data = $this->request->getData();

			// If content was manually edited, mark as non-auto
			if ($hasAutoField) {
				$contentChanged = false;
				if ($strategy === 'eav' && isset($data['content']) && $data['content'] !== $entry->get('content')) {
					$contentChanged = true;
				} elseif ($strategy === 'shadow_table') {
					foreach ($translatedFields as $field) {
						if (isset($data[$field]) && $data[$field] !== $entry->get($field)) {
							$contentChanged = true;

							break;
						}
					}
				}
				if ($contentChanged && !isset($data['auto'])) {
					$data['auto'] = false;
				}
			}

			$entry = $table->patchEntity($entry, $data);
			if ($table->save($entry)) {
				$this->Flash->success(__d('translate', 'The translation has been saved.'));

				return $this->redirect(['action' => 'entries', $tableName]);
			}

			$this->Flash->error(__d('translate', 'The translation could not be saved. Please try again.'));
		}

		// Get source text for reference
		$sourceText = null;
		$foreignKey = $entry->get($foreignKeyColumn);
		/** @var string|null $field */
		$field = $entry->get('field');
		if ($strategy === 'eav' && $foreignKey && $field) {
			$sourceText = $this->getSourceText($baseTableName, (int)$foreignKey, $field);
		}

		// Get glossary suggestions
		$glossarySuggestions = [];
		/** @var string|null $locale */
		$locale = $entry->get('locale');
		if ($sourceText && $locale) {
			$glossarySuggestions = $this->getGlossarySuggestions($sourceText, $locale);
		}

		$locales = $this->getLocalesForTable($connection, $tableName);

		$this->set(compact(
			'tableName',
			'baseTableName',
			'entry',
			'strategy',
			'translatedFields',
			'hasAutoField',
			'locales',
			'sourceText',
			'glossarySuggestions',
			'foreignKeyColumn',
		));

		return null;
	}

	/**
	 * Delete a translation entry
	 *
	 * @param string $tableName Translation table name
	 * @param int $id Entry ID
	 * @return \Cake\Http\Response|null
	 */
	public function delete(string $tableName, int $id) {
		$this->request->allowMethod(['post', 'delete']);

		if (!$this->validateTranslationTableName($tableName)) {
			throw new NotFoundException(__d('translate', 'Invalid translation table.'));
		}

		$table = $this->getTranslationTable($tableName);
		$entry = $table->get($id);

		if ($table->delete($entry)) {
			$this->Flash->success(__d('translate', 'The translation has been deleted.'));
		} else {
			$this->Flash->error(__d('translate', 'The translation could not be deleted. Please try again.'));
		}

		return $this->redirect(['action' => 'entries', $tableName]);
	}

	/**
	 * Auto-translate entries using configured translation engine
	 *
	 * @param string $tableName Translation table name
	 * @return \Cake\Http\Response|null
	 */
	public function autoTranslate(string $tableName) {
		$this->request->allowMethod(['post']);

		if (!$this->validateTranslationTableName($tableName)) {
			throw new NotFoundException(__d('translate', 'Invalid translation table.'));
		}

		$entryIds = $this->request->getData('entry_ids', []);
		$sourceLocale = $this->request->getData('source_locale', 'en');

		if (empty($entryIds) && !$this->request->getData('translate_all')) {
			$this->Flash->warning(__d('translate', 'No entries selected for translation.'));

			return $this->redirect(['action' => 'entries', $tableName]);
		}

		$connection = $this->getConnection();
		$schema = $connection->getSchemaCollection()->describe($tableName);
		$hasAutoField = $schema->hasColumn('auto');
		$strategy = $this->detectTranslationStrategy($schema);
		$foreignKeyColumn = $this->getForeignKeyColumn($schema);

		$service = new I18nTranslatorService();
		$baseTableName = $this->getBaseTableName($tableName);

		$table = $this->getTranslationTable($tableName);

		// Build query for entries to translate
		$query = $table->find();

		if (!empty($entryIds)) {
			$query->where(['id IN' => $entryIds]);
		} elseif ($this->request->getData('translate_all')) {
			// Only translate entries that are marked as auto or have empty content
			if ($hasAutoField) {
				if ($strategy === 'eav') {
					$query->where([
						'OR' => [
							['auto' => true],
							['content IS' => null],
							['content' => ''],
						],
					]);
				}
			}
			$targetLocale = $this->request->getData('target_locale');
			if ($targetLocale) {
				$query->where(['locale' => $targetLocale]);
			}
		}

		/** @var array<\Cake\ORM\Entity> $entries */
		$entries = $query->toArray();
		$translated = 0;
		$failed = 0;

		foreach ($entries as $entry) {
			// Get source text
			$sourceText = null;
			$foreignKey = $entry->get($foreignKeyColumn);
			/** @var string|null $field */
			$field = $entry->get('field');
			if ($strategy === 'eav' && $foreignKey && $field) {
				$sourceText = $this->getSourceText($baseTableName, (int)$foreignKey, $field);
			}

			if (!$sourceText) {
				$failed++;

				continue;
			}

			// Translate
			/** @var string|null $locale */
			$locale = $entry->get('locale');
			if (!$locale) {
				$failed++;

				continue;
			}

			$translatedText = $service->translate($sourceText, $locale, (string)$sourceLocale);

			if ($translatedText) {
				$entry->set('content', $translatedText);
				if ($hasAutoField) {
					$entry->set('auto', true);
				}

				if ($table->save($entry)) {
					$translated++;
				} else {
					$failed++;
				}
			} else {
				$failed++;
			}
		}

		if ($translated > 0) {
			$this->Flash->success(__d('translate', '{0} entries translated successfully.', $translated));
		}
		if ($failed > 0) {
			$this->Flash->warning(__d('translate', '{0} entries could not be translated.', $failed));
		}

		return $this->redirect(['action' => 'entries', $tableName]);
	}

	/**
	 * Batch mark entries as auto or manual
	 *
	 * @param string $tableName Translation table name
	 * @return \Cake\Http\Response|null
	 */
	public function batchUpdateAuto(string $tableName) {
		$this->request->allowMethod(['post']);

		if (!$this->validateTranslationTableName($tableName)) {
			throw new NotFoundException(__d('translate', 'Invalid translation table.'));
		}

		$connection = $this->getConnection();
		$schema = $connection->getSchemaCollection()->describe($tableName);
		if (!$schema->hasColumn('auto')) {
			$this->Flash->error(__d('translate', 'This table does not have an auto field.'));

			return $this->redirect(['action' => 'entries', $tableName]);
		}

		$entryIds = $this->request->getData('entry_ids', []);
		$autoValue = (bool)$this->request->getData('auto');

		if (empty($entryIds)) {
			$this->Flash->warning(__d('translate', 'No entries selected.'));

			return $this->redirect(['action' => 'entries', $tableName]);
		}

		$table = $this->getTranslationTable($tableName);
		$updated = $table->updateAll(
			['auto' => $autoValue],
			['id IN' => $entryIds],
		);

		$this->Flash->success(__d('translate', '{0} entries updated.', $updated));

		return $this->redirect(['action' => 'entries', $tableName]);
	}

	/**
	 * Get a database connection
	 *
	 * @return \Cake\Database\Connection
	 */
	protected function getConnection(): Connection {
		/** @var \Cake\Database\Connection $connection */
		$connection = ConnectionManager::get('default');

		return $connection;
	}

	/**
	 * Get a Table instance for a translation table
	 *
	 * @param string $tableName Table name
	 * @return \Cake\ORM\Table
	 */
	protected function getTranslationTable(string $tableName): \Cake\ORM\Table {
		return $this->fetchTable(Inflector::camelize($tableName), [
			'table' => $tableName,
		]);
	}

	/**
	 * Get info about all translation tables
	 *
	 * @param array<string> $allTables All table names
	 * @param \Cake\Database\Connection $connection Database connection
	 * @return array<string, array<string, mixed>>
	 */
	protected function getTranslationTablesInfo(array $allTables, Connection $connection): array {
		$translationTables = [];
		$systemPrefixes = ['cake_migrations', 'cake_seeds', 'translate_', 'queue_', 'audit_', 'phinxlog'];

		foreach ($allTables as $tableName) {
			$suffix = $this->getTranslationTableSuffix($tableName);
			if ($suffix === null) {
				continue;
			}

			$baseTableName = $this->getBaseTableName($tableName);

			// Skip system tables
			$isSystem = false;
			foreach ($systemPrefixes as $prefix) {
				if (str_starts_with($baseTableName, $prefix)) {
					$isSystem = true;

					break;
				}
			}
			if ($isSystem) {
				continue;
			}

			$schema = $connection->getSchemaCollection()->describe($tableName);
			$rowCount = $connection->execute("SELECT COUNT(*) as count FROM `{$tableName}`")->fetch('assoc')['count'] ?? 0;
			$strategy = $this->detectTranslationStrategy($schema);
			$hasAutoField = $schema->hasColumn('auto');

			// Count auto vs manual if auto field exists
			$autoCount = 0;
			$manualCount = 0;
			if ($hasAutoField) {
				$autoCount = $connection->execute("SELECT COUNT(*) as count FROM `{$tableName}` WHERE auto = 1")->fetch('assoc')['count'] ?? 0;
				$manualCount = (int)$rowCount - (int)$autoCount;
			}

			$translationTables[$tableName] = [
				'name' => $tableName,
				'base_table' => $baseTableName,
				'base_exists' => in_array($baseTableName, $allTables, true),
				'row_count' => (int)$rowCount,
				'strategy' => $strategy,
				'suffix' => $suffix,
				'has_auto_field' => $hasAutoField,
				'auto_count' => (int)$autoCount,
				'manual_count' => (int)$manualCount,
			];
		}

		return $translationTables;
	}

	/**
	 * Get available locales across all translation tables
	 *
	 * @param array<string, array<string, mixed>> $translationTables Translation tables info
	 * @param \Cake\Database\Connection $connection Database connection
	 * @return array<string>
	 */
	protected function getAvailableLocales(array $translationTables, Connection $connection): array {
		$locales = [];

		foreach ($translationTables as $info) {
			$tableName = $info['name'];
			$result = $connection->execute("SELECT DISTINCT locale FROM `{$tableName}`")->fetchAll('assoc');
			foreach ($result as $row) {
				if ($row['locale'] && !in_array($row['locale'], $locales, true)) {
					$locales[] = $row['locale'];
				}
			}
		}

		sort($locales);

		return $locales;
	}

	/**
	 * Validate translation table name
	 *
	 * @param string|null $tableName Table name
	 * @return bool
	 */
	protected function validateTranslationTableName(?string $tableName): bool {
		if (!$tableName) {
			return false;
		}

		// Must end with a known suffix
		if ($this->getTranslationTableSuffix($tableName) === null) {
			return false;
		}

		// Basic SQL injection prevention
		if (preg_match('/[^a-z0-9_]/i', $tableName)) {
			return false;
		}

		return true;
	}

	/**
	 * Get the translation table suffix if valid
	 *
	 * @param string $tableName Table name
	 * @return string|null The suffix or null if not a translation table
	 */
	protected function getTranslationTableSuffix(string $tableName): ?string {
		foreach (static::TABLE_SUFFIXES as $suffix) {
			if (str_ends_with($tableName, $suffix)) {
				return $suffix;
			}
		}

		return null;
	}

	/**
	 * Get base table name from translation table name
	 *
	 * @param string $tableName Translation table name
	 * @return string Base table name
	 */
	protected function getBaseTableName(string $tableName): string {
		foreach (static::TABLE_SUFFIXES as $suffix) {
			if (str_ends_with($tableName, $suffix)) {
				return substr($tableName, 0, -strlen($suffix));
			}
		}

		return $tableName;
	}

	/**
	 * Detect translation strategy from schema
	 *
	 * @param \Cake\Database\Schema\TableSchemaInterface $schema Schema
	 * @return string 'eav' or 'shadow_table'
	 */
	protected function detectTranslationStrategy(\Cake\Database\Schema\TableSchemaInterface $schema): string {
		$columns = $schema->columns();

		// EAV strategy has: id, locale, model, foreign_key, field, content
		if (in_array('field', $columns, true) && in_array('content', $columns, true)) {
			return 'eav';
		}

		// Shadow table strategy has: id, locale, and then the actual field columns
		return 'shadow_table';
	}

	/**
	 * Get the foreign key column name from schema
	 *
	 * @param \Cake\Database\Schema\TableSchemaInterface $schema Schema
	 * @return string Foreign key column name ('foreign_key' or 'id')
	 */
	protected function getForeignKeyColumn(\Cake\Database\Schema\TableSchemaInterface $schema): string {
		$columns = $schema->columns();

		// EAV uses 'foreign_key'
		if (in_array('foreign_key', $columns, true)) {
			return 'foreign_key';
		}

		// ShadowTable uses 'id' as the foreign key
		return 'id';
	}

	/**
	 * Get translated fields from schema
	 *
	 * @param \Cake\Database\Schema\TableSchemaInterface $schema Schema
	 * @param string $strategy Translation strategy
	 * @return array<string>
	 */
	protected function getTranslatedFieldsFromSchema(\Cake\Database\Schema\TableSchemaInterface $schema, string $strategy): array {
		if ($strategy === 'eav') {
			return ['content'];
		}

		// Shadow table strategy - all columns except system columns
		$excludeColumns = ['id', 'locale', 'foreign_key', 'auto', 'created', 'modified'];

		return array_values(array_diff($schema->columns(), $excludeColumns));
	}

	/**
	 * Get locales for a specific table
	 *
	 * @param \Cake\Database\Connection $connection Database connection
	 * @param string $tableName Table name
	 * @return array<string>
	 */
	protected function getLocalesForTable(Connection $connection, string $tableName): array {
		$result = $connection->execute("SELECT DISTINCT locale FROM `{$tableName}` ORDER BY locale")->fetchAll('assoc');

		return array_column($result, 'locale');
	}

	/**
	 * Get source text from base table
	 *
	 * @param string $baseTableName Base table name
	 * @param int $foreignKey Foreign key
	 * @param string $field Field name
	 * @return string|null
	 */
	protected function getSourceText(string $baseTableName, int $foreignKey, string $field): ?string {
		try {
			$baseTable = $this->fetchTable(Inflector::camelize($baseTableName));
			$record = $baseTable->get($foreignKey);

			return $record->get($field);
		} catch (\Exception $e) {
			return null;
		}
	}

	/**
	 * Get glossary suggestions from PO strings
	 *
	 * @param string $text Text to find suggestions for
	 * @param string $locale Target locale
	 * @return array<array<string, string>>
	 */
	protected function getGlossarySuggestions(string $text, string $locale): array {
		$suggestions = [];

		try {
			/** @var \Translate\Model\Table\TranslateTermsTable $termsTable */
			$termsTable = $this->fetchTable('Translate.TranslateTerms');

			// Extract words/phrases from text
			$words = preg_split('/\s+/', $text);
			if ($words === false) {
				return [];
			}
			$words = array_filter($words, fn($w): bool => strlen((string)$w) > 2);

			if (empty($words)) {
				return [];
			}

			// Search for matching terms
			$query = $termsTable->find()
				->contain(['TranslateStrings', 'TranslateLocales'])
				->where(['TranslateLocales.locale' => $locale])
				->limit(10);

			$conditions = ['OR' => []];
			foreach (array_slice($words, 0, 5) as $word) {
				$conditions['OR'][] = ['TranslateStrings.name LIKE' => '%' . $word . '%'];
			}
			if (!empty($conditions['OR'])) {
				$query->where($conditions);
			}

			foreach ($query as $term) {
				if (!$term instanceof \Cake\Datasource\EntityInterface) {
					continue;
				}
				/** @var \Cake\Datasource\EntityInterface|null $translateString */
				$translateString = $term->get('translate_string');
				if (!$translateString instanceof \Cake\Datasource\EntityInterface) {
					continue;
				}
				$suggestions[] = [
					'source' => (string)$translateString->get('name'),
					'translation' => (string)$term->get('content'),
				];
			}
		} catch (\Exception $e) {
			// Translate tables might not exist
		}

		return $suggestions;
	}

}
