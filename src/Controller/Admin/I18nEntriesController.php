<?php
declare(strict_types=1);

namespace Translate\Controller\Admin;

use Cake\Database\Schema\CollectionInterface;
use Cake\Datasource\ConnectionManager;
use Cake\Http\Exception\NotFoundException;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\ORM\Query\SelectQuery;
use Cake\Utility\Inflector;
use Translate\Controller\TranslateAppController;
use Translate\Service\I18nTranslatorService;

/**
 * I18nEntries Controller
 *
 * Provides CRUD operations for TranslateBehavior i18n entries (shadow tables).
 * Works with any *_i18n table that follows CakePHP's TranslateBehavior conventions.
 *
 * @property \Translate\Controller\Component\TranslationComponent $Translation
 */
class I18nEntriesController extends TranslateAppController {

	use LocatorAwareTrait;

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
	 * Index - list all shadow tables with entry counts
	 *
	 * @return void
	 */
	public function index(): void {
		/** @var \Cake\Database\Connection $connection */
		$connection = ConnectionManager::get('default');
		$schemaCollection = $connection->getSchemaCollection();
		$allTables = $schemaCollection->listTables();

		$shadowTables = $this->getShadowTablesInfo($allTables, $connection);
		$locales = $this->getAvailableLocales($shadowTables, $connection);

		$this->set(compact('shadowTables', 'locales'));
	}

	/**
	 * Entries - list entries for a specific shadow table
	 *
	 * @param string $tableName Shadow table name (e.g., 'articles_i18n')
	 * @return \Cake\Http\Response|null
	 */
	public function entries(string $tableName) {
		if (!$this->validateShadowTableName($tableName)) {
			$this->Flash->error(__d('translate', 'Invalid shadow table name.'));

			return $this->redirect(['action' => 'index']);
		}

		/** @var \Cake\Database\Connection $connection */
		$connection = ConnectionManager::get('default');
		$schemaCollection = $connection->getSchemaCollection();

		if (!in_array($tableName, $schemaCollection->listTables())) {
			$this->Flash->error(__d('translate', 'Shadow table not found.'));

			return $this->redirect(['action' => 'index']);
		}

		$baseTableName = substr($tableName, 0, -5);
		$schema = $schemaCollection->describe($tableName);
		$strategy = $this->detectTranslationStrategy($schema);
		$translatedFields = $this->getTranslatedFieldsFromSchema($schema, $strategy);
		$locales = $this->getLocalesForTable($connection, $tableName, $strategy);

		// Build query
		$table = $this->getI18nTable($tableName);
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
				$query->where($conditions);
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
		));

		return null;
	}

	/**
	 * View a single i18n entry
	 *
	 * @param string $tableName Shadow table name
	 * @param int $id Entry ID
	 * @return \Cake\Http\Response|null
	 */
	public function view(string $tableName, int $id) {
		if (!$this->validateShadowTableName($tableName)) {
			throw new NotFoundException(__d('translate', 'Invalid shadow table.'));
		}

		$table = $this->getI18nTable($tableName);
		$entry = $table->get($id);

		$baseTableName = substr($tableName, 0, -5);
		$schema = ConnectionManager::get('default')->getSchemaCollection()->describe($tableName);
		$strategy = $this->detectTranslationStrategy($schema);
		$translatedFields = $this->getTranslatedFieldsFromSchema($schema, $strategy);
		$hasAutoField = $schema->hasColumn('auto');

		// Get base record info if possible
		$baseRecord = null;
		$foreignKey = $entry->foreign_key ?? null;
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
		if ($strategy === 'eav' && $entry->content) {
			$glossarySuggestions = $this->getGlossarySuggestions($entry->content, $entry->locale);
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
		));

		return null;
	}

	/**
	 * Edit an i18n entry
	 *
	 * @param string $tableName Shadow table name
	 * @param int $id Entry ID
	 * @return \Cake\Http\Response|null
	 */
	public function edit(string $tableName, int $id) {
		if (!$this->validateShadowTableName($tableName)) {
			throw new NotFoundException(__d('translate', 'Invalid shadow table.'));
		}

		$table = $this->getI18nTable($tableName);
		$entry = $table->get($id);

		$schema = ConnectionManager::get('default')->getSchemaCollection()->describe($tableName);
		$strategy = $this->detectTranslationStrategy($schema);
		$translatedFields = $this->getTranslatedFieldsFromSchema($schema, $strategy);
		$hasAutoField = $schema->hasColumn('auto');
		$baseTableName = substr($tableName, 0, -5);

		if ($this->request->is(['patch', 'post', 'put'])) {
			$data = $this->request->getData();

			// If content was manually edited, mark as non-auto
			if ($hasAutoField && isset($data['content']) && $data['content'] !== $entry->content) {
				$data['auto'] = false;
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
		if ($strategy === 'eav' && $entry->foreign_key && $entry->field) {
			$sourceText = $this->getSourceText($baseTableName, $entry->foreign_key, $entry->field);
		}

		// Get glossary suggestions
		$glossarySuggestions = [];
		if ($sourceText) {
			$glossarySuggestions = $this->getGlossarySuggestions($sourceText, $entry->locale);
		}

		$locales = $this->getLocalesForTable(
			ConnectionManager::get('default'),
			$tableName,
			$strategy,
		);

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
		));

		return null;
	}

	/**
	 * Delete an i18n entry
	 *
	 * @param string $tableName Shadow table name
	 * @param int $id Entry ID
	 * @return \Cake\Http\Response
	 */
	public function delete(string $tableName, int $id) {
		$this->request->allowMethod(['post', 'delete']);

		if (!$this->validateShadowTableName($tableName)) {
			throw new NotFoundException(__d('translate', 'Invalid shadow table.'));
		}

		$table = $this->getI18nTable($tableName);
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
	 * @param string $tableName Shadow table name
	 * @return \Cake\Http\Response
	 */
	public function autoTranslate(string $tableName) {
		$this->request->allowMethod(['post']);

		if (!$this->validateShadowTableName($tableName)) {
			throw new NotFoundException(__d('translate', 'Invalid shadow table.'));
		}

		$entryIds = $this->request->getData('entry_ids', []);
		$targetLocale = $this->request->getData('target_locale');
		$sourceLocale = $this->request->getData('source_locale', 'en');

		if (empty($entryIds) && !$this->request->getData('translate_all')) {
			$this->Flash->warning(__d('translate', 'No entries selected for translation.'));

			return $this->redirect(['action' => 'entries', $tableName]);
		}

		$schema = ConnectionManager::get('default')->getSchemaCollection()->describe($tableName);
		$hasAutoField = $schema->hasColumn('auto');
		$strategy = $this->detectTranslationStrategy($schema);

		$service = new I18nTranslatorService();
		$baseTableName = substr($tableName, 0, -5);

		$table = $this->getI18nTable($tableName);

		// Build query for entries to translate
		$query = $table->find();

		if (!empty($entryIds)) {
			$query->where(['id IN' => $entryIds]);
		} elseif ($this->request->getData('translate_all')) {
			// Only translate entries that are marked as auto or have empty content
			if ($hasAutoField) {
				$query->where([
					'OR' => [
						['auto' => true],
						['content IS' => null],
						['content' => ''],
					],
				]);
			}
			if ($targetLocale) {
				$query->where(['locale' => $targetLocale]);
			}
		}

		$entries = $query->toArray();
		$translated = 0;
		$failed = 0;

		foreach ($entries as $entry) {
			// Get source text
			$sourceText = null;
			if ($strategy === 'eav') {
				$sourceText = $this->getSourceText($baseTableName, $entry->foreign_key, $entry->field);
			}

			if (!$sourceText) {
				$failed++;

				continue;
			}

			// Translate
			$translatedText = $service->translate($sourceText, $entry->locale, $sourceLocale);

			if ($translatedText) {
				$entry->content = $translatedText;
				if ($hasAutoField) {
					$entry->auto = true;
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
	 * @param string $tableName Shadow table name
	 * @return \Cake\Http\Response
	 */
	public function batchUpdateAuto(string $tableName) {
		$this->request->allowMethod(['post']);

		if (!$this->validateShadowTableName($tableName)) {
			throw new NotFoundException(__d('translate', 'Invalid shadow table.'));
		}

		$schema = ConnectionManager::get('default')->getSchemaCollection()->describe($tableName);
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

		$table = $this->getI18nTable($tableName);
		$updated = $table->updateAll(
			['auto' => $autoValue],
			['id IN' => $entryIds],
		);

		$this->Flash->success(__d('translate', '{0} entries updated.', $updated));

		return $this->redirect(['action' => 'entries', $tableName]);
	}

	/**
	 * Get a Table instance for an i18n shadow table
	 *
	 * @param string $tableName Table name
	 * @return \Cake\ORM\Table
	 */
	protected function getI18nTable(string $tableName): \Cake\ORM\Table {
		return $this->fetchTable(Inflector::camelize($tableName), [
			'table' => $tableName,
		]);
	}

	/**
	 * Get info about all shadow tables
	 *
	 * @param array<string> $allTables All table names
	 * @param \Cake\Database\Connection $connection Database connection
	 * @return array<string, array<string, mixed>>
	 */
	protected function getShadowTablesInfo(array $allTables, $connection): array {
		$shadowTables = [];
		$systemPrefixes = ['cake_migrations', 'cake_seeds', 'translate_', 'queue_', 'audit_', 'phinxlog'];

		foreach ($allTables as $tableName) {
			if (!str_ends_with($tableName, '_i18n')) {
				continue;
			}

			$baseTableName = substr($tableName, 0, -5);

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
				$manualCount = $rowCount - $autoCount;
			}

			$shadowTables[$tableName] = [
				'name' => $tableName,
				'base_table' => $baseTableName,
				'base_exists' => in_array($baseTableName, $allTables),
				'row_count' => (int)$rowCount,
				'strategy' => $strategy,
				'has_auto_field' => $hasAutoField,
				'auto_count' => (int)$autoCount,
				'manual_count' => (int)$manualCount,
			];
		}

		return $shadowTables;
	}

	/**
	 * Get available locales across all shadow tables
	 *
	 * @param array<string, array<string, mixed>> $shadowTables Shadow tables info
	 * @param \Cake\Database\Connection $connection Database connection
	 * @return array<string>
	 */
	protected function getAvailableLocales(array $shadowTables, $connection): array {
		$locales = [];

		foreach ($shadowTables as $info) {
			$tableName = $info['name'];
			$result = $connection->execute("SELECT DISTINCT locale FROM `{$tableName}`")->fetchAll('assoc');
			foreach ($result as $row) {
				if ($row['locale'] && !in_array($row['locale'], $locales)) {
					$locales[] = $row['locale'];
				}
			}
		}

		sort($locales);

		return $locales;
	}

	/**
	 * Validate shadow table name
	 *
	 * @param string|null $tableName Table name
	 * @return bool
	 */
	protected function validateShadowTableName(?string $tableName): bool {
		if (!$tableName) {
			return false;
		}

		// Must end with _i18n
		if (!str_ends_with($tableName, '_i18n')) {
			return false;
		}

		// Basic SQL injection prevention
		if (preg_match('/[^a-z0-9_]/i', $tableName)) {
			return false;
		}

		return true;
	}

	/**
	 * Detect translation strategy from schema
	 *
	 * @param \Cake\Database\Schema\TableSchemaInterface $schema Schema
	 * @return string 'eav' or 'shadow_table'
	 */
	protected function detectTranslationStrategy($schema): string {
		$columns = $schema->columns();

		if (in_array('field', $columns) && in_array('content', $columns)) {
			return 'eav';
		}

		return 'shadow_table';
	}

	/**
	 * Get translated fields from schema
	 *
	 * @param \Cake\Database\Schema\TableSchemaInterface $schema Schema
	 * @param string $strategy Translation strategy
	 * @return array<string>
	 */
	protected function getTranslatedFieldsFromSchema($schema, string $strategy): array {
		if ($strategy === 'eav') {
			return ['content'];
		}

		$excludeColumns = ['id', 'locale', 'foreign_key', 'auto', 'created', 'modified'];

		return array_values(array_diff($schema->columns(), $excludeColumns));
	}

	/**
	 * Get locales for a specific table
	 *
	 * @param \Cake\Database\Connection $connection Database connection
	 * @param string $tableName Table name
	 * @param string $strategy Translation strategy
	 * @return array<string>
	 */
	protected function getLocalesForTable($connection, string $tableName, string $strategy): array {
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
			$words = array_filter($words, fn ($w) => strlen($w) > 2);

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
			$query->where($conditions);

			foreach ($query as $term) {
				$suggestions[] = [
					'source' => $term->translate_string->name,
					'translation' => $term->content,
				];
			}
		} catch (\Exception $e) {
			// Translate tables might not exist
		}

		return $suggestions;
	}

}
