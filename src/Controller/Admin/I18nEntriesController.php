<?php
declare(strict_types=1);

namespace Translate\Controller\Admin;

use Cake\Core\Configure;
use Cake\Database\Connection;
use Cake\Database\Schema\TableSchemaInterface;
use Cake\Datasource\ConnectionManager;
use Cake\Datasource\EntityInterface;
use Cake\Http\Exception\NotFoundException;
use Cake\ORM\Entity;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\ORM\Table;
use Cake\Utility\Inflector;
use Exception;
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
	 *
	 * @var array<string>
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
	 * Entries - list base table records with their translation status
	 *
	 * Shows all records from the base table and indicates which have translations.
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
		$foreignKeyColumn = $this->getForeignKeyColumn($schema);
		$hasAutoField = $schema->hasColumn('auto');

		// Get configured locales from app or use existing ones from table
		$configuredLocales = $this->getConfiguredLocales();
		$existingLocales = $this->getLocalesForTable($connection, $tableName);
		$locales = array_unique(array_merge($configuredLocales, $existingLocales));
		sort($locales);

		// For shadow table strategy, show base table records with translation status
		if ($strategy === 'shadow_table' && in_array($baseTableName, $schemaCollection->listTables(), true)) {
			return $this->entriesWithBaseTable(
				$tableName,
				$baseTableName,
				$locales,
				$translatedFields,
				$hasAutoField,
				$strategy,
				$foreignKeyColumn,
			);
		}

		// For EAV strategy or when base table doesn't exist, show translation entries directly
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
		$this->set('showBaseRecords', false);

		return null;
	}

	/**
	 * Show entries with base table records and their translation status
	 *
	 * @param string $tableName Translation table name
	 * @param string $baseTableName Base table name
	 * @param array<string> $locales Available locales
	 * @param array<string> $translatedFields Fields that are translated
	 * @param bool $hasAutoField Whether the translation table has an auto field
	 * @param string $strategy Translation strategy
	 * @param string $foreignKeyColumn Foreign key column name
	 * @return \Cake\Http\Response|null
	 */
	protected function entriesWithBaseTable(
		string $tableName,
		string $baseTableName,
		array $locales,
		array $translatedFields,
		bool $hasAutoField,
		string $strategy,
		string $foreignKeyColumn,
	) {
		$connection = $this->getConnection();

		// Get base table records
		$baseTable = $this->getTranslationTable($baseTableName);
		$query = $baseTable->find();

		// Try to get a display field for better identification
		$baseSchema = $connection->getSchemaCollection()->describe($baseTableName);
		$displayField = $this->guessDisplayField($baseSchema);

		// Apply search filter
		$search = $this->request->getQuery('search');
		if ($search && $displayField) {
			$query->where([$displayField . ' LIKE' => '%' . $search . '%']);
		}

		$query->orderBy(['id' => 'ASC']);
		$baseRecords = $this->paginate($query);

		// Get translation status for each base record
		$baseRecordsArray = iterator_to_array($baseRecords);
		$baseIds = array_map(fn ($r) => $r->id, $baseRecordsArray);

		// Track which fields have content in base records
		$baseFieldStatus = [];
		foreach ($baseRecordsArray as $record) {
			$baseFieldStatus[$record->id] = [];
			foreach ($translatedFields as $field) {
				$baseFieldStatus[$record->id][$field] = !empty($record->get($field));
			}
		}

		$translationStatus = [];
		if (!empty($baseIds)) {
			$translationTable = $this->getTranslationTable($tableName);
			/** @var array<\Cake\ORM\Entity> $translations */
			$translations = $translationTable->find()
				->where(['id IN' => $baseIds])
				->toArray();

			foreach ($translations as $translation) {
				/** @var int $id */
				$id = $translation->get('id');
				/** @var string $locale */
				$locale = $translation->get('locale');
				if (!isset($translationStatus[$id])) {
					$translationStatus[$id] = [];
				}
				$translationStatus[$id][$locale] = [
					'exists' => true,
					'auto' => $hasAutoField ? (bool)$translation->get('auto') : null,
					'fields' => [],
				];
				// Check which fields have content
				foreach ($translatedFields as $field) {
					$value = $translation->get($field);
					$translationStatus[$id][$locale]['fields'][$field] = !empty($value);
				}
			}
		}

		// Source locale (base record language) - filter it out, no translations needed
		$sourceLocale = Configure::read('App.defaultLocale') ?? Configure::read('I18n.defaultLocale') ?? 'en_US';
		$locales = array_values(array_filter($locales, fn ($l) => $l !== $sourceLocale));

		$this->set(compact(
			'tableName',
			'baseTableName',
			'baseRecords',
			'translationStatus',
			'baseFieldStatus',
			'locales',
			'translatedFields',
			'hasAutoField',
			'strategy',
			'foreignKeyColumn',
			'displayField',
		));
		$this->set('showBaseRecords', true);

		return $this->render('entries_base');
	}

	/**
	 * View all translations for a base record
	 *
	 * @param string $tableName Translation table name
	 * @param int $id Base record ID
	 * @return \Cake\Http\Response|null
	 */
	public function viewRecord(string $tableName, int $id) {
		if (!$this->validateTranslationTableName($tableName)) {
			throw new NotFoundException(__d('translate', 'Invalid translation table.'));
		}

		$baseTableName = $this->getBaseTableName($tableName);
		$connection = $this->getConnection();
		$schema = $connection->getSchemaCollection()->describe($tableName);
		$translatedFields = $this->getTranslatedFieldsFromSchema($schema, $this->detectTranslationStrategy($schema));
		$hasAutoField = $schema->hasColumn('auto');

		// Get base record
		$baseTable = $this->getTranslationTable($baseTableName);
		$baseRecord = $baseTable->get($id);

		$baseSchema = $connection->getSchemaCollection()->describe($baseTableName);
		$displayField = $this->guessDisplayField($baseSchema);

		// Get all translations for this record
		$translationTable = $this->getTranslationTable($tableName);
		/** @var array<\Cake\ORM\Entity> $translations */
		$translations = $translationTable->find()
			->where(['id' => $id])
			->toArray();

		// Get configured locales
		$configuredLocales = $this->getConfiguredLocales();
		/** @var array<string> $existingLocales */
		$existingLocales = array_map(fn ($t) => $t->get('locale'), $translations);
		$locales = array_unique(array_merge($configuredLocales, $existingLocales));
		sort($locales);

		// Index translations by locale
		$translationsByLocale = [];
		foreach ($translations as $translation) {
			$translationsByLocale[$translation->get('locale')] = $translation;
		}

		// Source locale (base record language) - filter it out, no translations needed
		$sourceLocale = Configure::read('App.defaultLocale') ?? Configure::read('I18n.defaultLocale') ?? 'en_US';
		$locales = array_values(array_filter($locales, fn ($l) => $l !== $sourceLocale));

		$this->set(compact(
			'tableName',
			'baseTableName',
			'baseRecord',
			'translations',
			'translationsByLocale',
			'locales',
			'translatedFields',
			'hasAutoField',
			'displayField',
		));

		return null;
	}

	/**
	 * Add a translation for a base record
	 *
	 * @param string $tableName Translation table name
	 * @param int $id Base record ID
	 * @param string $locale Locale code
	 * @return \Cake\Http\Response|null
	 */
	public function addTranslation(string $tableName, int $id, string $locale) {
		if (!$this->validateTranslationTableName($tableName)) {
			throw new NotFoundException(__d('translate', 'Invalid translation table.'));
		}

		// Reject attempts to add translation for source locale
		$sourceLocale = Configure::read('App.defaultLocale') ?? Configure::read('I18n.defaultLocale') ?? 'en_US';
		if ($locale === $sourceLocale) {
			$this->Flash->error(__d('translate', 'Cannot add translation for the source locale. Edit the base record instead.'));

			return $this->redirect(['action' => 'entries', $tableName]);
		}

		$baseTableName = $this->getBaseTableName($tableName);
		$connection = $this->getConnection();
		$schema = $connection->getSchemaCollection()->describe($tableName);
		$translatedFields = $this->getTranslatedFieldsFromSchema($schema, $this->detectTranslationStrategy($schema));
		$hasAutoField = $schema->hasColumn('auto');

		// Get base record for reference
		$baseTable = $this->getTranslationTable($baseTableName);
		$baseRecord = $baseTable->get($id);

		$baseSchema = $connection->getSchemaCollection()->describe($baseTableName);
		$displayField = $this->guessDisplayField($baseSchema);

		// Check if translation already exists
		$translationTable = $this->getTranslationTable($tableName);
		$existing = $translationTable->find()
			->where(['id' => $id, 'locale' => $locale])
			->first();

		if ($existing) {
			return $this->redirect(['action' => 'editTranslation', $tableName, $id, $locale]);
		}

		// Create new translation entity
		$translation = $translationTable->newEmptyEntity();
		$translation->set('id', $id);
		$translation->set('locale', $locale);

		if ($this->request->is(['patch', 'post', 'put'])) {
			$data = $this->request->getData();
			$data['id'] = $id;
			$data['locale'] = $locale;

			$translation = $translationTable->patchEntity($translation, $data);
			if ($translationTable->save($translation)) {
				$this->Flash->success(__d('translate', 'Translation saved successfully.'));

				return $this->redirect(['action' => 'viewRecord', $tableName, $id]);
			}

			$this->Flash->error(__d('translate', 'Could not save translation. Please try again.'));
		}

		$this->set(compact(
			'tableName',
			'baseTableName',
			'baseRecord',
			'translation',
			'locale',
			'translatedFields',
			'hasAutoField',
			'displayField',
		));

		return $this->render('translation_form');
	}

	/**
	 * Edit a translation for a base record
	 *
	 * @param string $tableName Translation table name
	 * @param int $id Base record ID
	 * @param string $locale Locale code
	 * @return \Cake\Http\Response|null
	 */
	public function editTranslation(string $tableName, int $id, string $locale) {
		if (!$this->validateTranslationTableName($tableName)) {
			throw new NotFoundException(__d('translate', 'Invalid translation table.'));
		}

		// Reject attempts to edit translation for source locale
		$sourceLocale = Configure::read('App.defaultLocale') ?? Configure::read('I18n.defaultLocale') ?? 'en_US';
		if ($locale === $sourceLocale) {
			$this->Flash->error(__d('translate', 'Cannot edit translation for the source locale. Edit the base record instead.'));

			return $this->redirect(['action' => 'entries', $tableName]);
		}

		$baseTableName = $this->getBaseTableName($tableName);
		$connection = $this->getConnection();
		$schema = $connection->getSchemaCollection()->describe($tableName);
		$strategy = $this->detectTranslationStrategy($schema);
		$translatedFields = $this->getTranslatedFieldsFromSchema($schema, $strategy);
		$hasAutoField = $schema->hasColumn('auto');

		// Get base record for reference
		$baseTable = $this->getTranslationTable($baseTableName);
		$baseRecord = $baseTable->get($id);

		$baseSchema = $connection->getSchemaCollection()->describe($baseTableName);
		$displayField = $this->guessDisplayField($baseSchema);

		// Get existing translation
		$translationTable = $this->getTranslationTable($tableName);
		$translation = $translationTable->find()
			->where(['id' => $id, 'locale' => $locale])
			->first();

		if (!$translation) {
			return $this->redirect(['action' => 'addTranslation', $tableName, $id, $locale]);
		}
		assert($translation instanceof Entity);

		if ($this->request->is(['patch', 'post', 'put'])) {
			$data = $this->request->getData();

			// If content changed and auto field exists, mark as manual
			if ($hasAutoField && !isset($data['auto'])) {
				$contentChanged = false;
				foreach ($translatedFields as $field) {
					if (isset($data[$field]) && $data[$field] !== $translation->get($field)) {
						$contentChanged = true;

						break;
					}
				}
				if ($contentChanged) {
					$data['auto'] = false;
				}
			}

			$translation = $translationTable->patchEntity($translation, $data);
			if ($translationTable->save($translation)) {
				$this->Flash->success(__d('translate', 'Translation updated successfully.'));

				return $this->redirect(['action' => 'viewRecord', $tableName, $id]);
			}

			$this->Flash->error(__d('translate', 'Could not save translation. Please try again.'));
		}

		$this->set(compact(
			'tableName',
			'baseTableName',
			'baseRecord',
			'translation',
			'locale',
			'translatedFields',
			'hasAutoField',
			'displayField',
		));

		return $this->render('translation_form');
	}

	/**
	 * Auto-translate a single record to specified locales
	 *
	 * @param string $tableName Translation table name
	 * @param int $id Base record ID
	 * @return \Cake\Http\Response|null
	 */
	public function autoTranslateRecord(string $tableName, int $id) {
		$this->request->allowMethod(['post']);

		if (!$this->validateTranslationTableName($tableName)) {
			throw new NotFoundException(__d('translate', 'Invalid translation table.'));
		}

		$baseTableName = $this->getBaseTableName($tableName);
		$connection = $this->getConnection();
		$schema = $connection->getSchemaCollection()->describe($tableName);
		$strategy = $this->detectTranslationStrategy($schema);
		$translatedFields = $this->getTranslatedFieldsFromSchema($schema, $strategy);
		$hasAutoField = $schema->hasColumn('auto');

		// Get base record
		$baseTable = $this->getTranslationTable($baseTableName);
		$baseRecord = $baseTable->get($id);

		// Get target locales from request or use all configured
		/** @var array<string> $targetLocales */
		$targetLocales = $this->request->getData('locales', []);
		if (empty($targetLocales)) {
			$targetLocales = $this->getConfiguredLocales();
		}

		// Get source locale (default locale of the app)
		$sourceLocale = Configure::read('App.defaultLocale') ?? Configure::read('I18n.defaultLocale') ?? 'en_US';

		$service = new I18nTranslatorService();
		$translationTable = $this->getTranslationTable($tableName);

		$translated = 0;
		$failed = 0;

		foreach ($targetLocales as $locale) {
			// Skip source locale
			if ($locale === $sourceLocale) {
				continue;
			}

			// Get or create translation entry
			/** @var \Cake\ORM\Entity|null $translation */
			$translation = $translationTable->find()
				->where(['id' => $id, 'locale' => $locale])
				->first();

			if (!$translation) {
				/** @var \Cake\ORM\Entity $translation */
				$translation = $translationTable->newEntity([
					'id' => $id,
					'locale' => $locale,
				]);
			}

			$hasChanges = false;
			foreach ($translatedFields as $field) {
				$sourceText = $baseRecord->get($field);
				if (empty($sourceText)) {
					continue;
				}

				$translatedText = $service->translate($sourceText, $locale, $sourceLocale);
				if ($translatedText) {
					$translation->set($field, $translatedText);
					$hasChanges = true;
				}
			}

			if ($hasChanges) {
				if ($hasAutoField) {
					$translation->set('auto', true);
				}

				if ($translationTable->save($translation)) {
					$translated++;
				} else {
					$failed++;
				}
			}
		}

		if ($translated > 0) {
			$this->Flash->success(__d('translate', '{0} locale(s) translated successfully.', $translated));
		}
		if ($failed > 0) {
			$this->Flash->warning(__d('translate', '{0} locale(s) could not be translated.', $failed));
		}
		if ($translated === 0 && $failed === 0) {
			$this->Flash->info(__d('translate', 'No translations needed.'));
		}

		return $this->redirect(['action' => 'viewRecord', $tableName, $id]);
	}

	/**
	 * Batch auto-translate multiple records
	 *
	 * @param string $tableName Translation table name
	 * @return \Cake\Http\Response|null
	 */
	public function autoTranslateBatch(string $tableName) {
		$this->request->allowMethod(['post']);

		if (!$this->validateTranslationTableName($tableName)) {
			$this->Flash->error(__d('translate', 'Invalid translation table name.'));

			return $this->redirect(['action' => 'index']);
		}

		$baseTableName = $this->getBaseTableName($tableName);
		$connection = $this->getConnection();
		$schemaCollection = $connection->getSchemaCollection();

		if (!in_array($baseTableName, $schemaCollection->listTables(), true)) {
			$this->Flash->error(__d('translate', 'Base table not found.'));

			return $this->redirect(['action' => 'index']);
		}

		$schema = $schemaCollection->describe($tableName);
		$strategy = $this->detectTranslationStrategy($schema);
		$translatedFields = $this->getTranslatedFieldsFromSchema($schema, $strategy);
		$hasAutoField = $schema->hasColumn('auto');

		// Get selected record IDs
		/** @var array<int> $recordIds */
		$recordIds = $this->request->getData('record_ids', []);
		if (empty($recordIds)) {
			$this->Flash->warning(__d('translate', 'No records selected.'));

			return $this->redirect(['action' => 'entries', $tableName]);
		}

		// Get target locales
		/** @var array<string> $targetLocales */
		$targetLocales = $this->request->getData('locales', []);
		if (empty($targetLocales)) {
			$targetLocales = $this->getConfiguredLocales();
		}

		// Get source locale
		$sourceLocale = Configure::read('App.defaultLocale') ?? Configure::read('I18n.defaultLocale') ?? 'en_US';

		$service = new I18nTranslatorService();
		$baseTable = $this->getTranslationTable($baseTableName);
		$translationTable = $this->getTranslationTable($tableName);

		$translated = 0;
		$failed = 0;

		foreach ($recordIds as $recordId) {
			try {
				$baseRecord = $baseTable->get($recordId);
			} catch (Exception $e) {
				$failed++;

				continue;
			}

			foreach ($targetLocales as $locale) {
				// Skip source locale
				if ($locale === $sourceLocale) {
					continue;
				}

				// Get or create translation entry
				/** @var \Cake\ORM\Entity|null $translation */
				$translation = $translationTable->find()
					->where(['id' => $recordId, 'locale' => $locale])
					->first();

				if (!$translation) {
					/** @var \Cake\ORM\Entity $translation */
					$translation = $translationTable->newEntity([
						'id' => $recordId,
						'locale' => $locale,
					]);
				}

				$hasChanges = false;
				foreach ($translatedFields as $field) {
					$sourceText = $baseRecord->get($field);
					if (empty($sourceText)) {
						continue;
					}

					$translatedText = $service->translate($sourceText, $locale, $sourceLocale);
					if ($translatedText) {
						$translation->set($field, $translatedText);
						$hasChanges = true;
					}
				}

				if ($hasChanges) {
					if ($hasAutoField) {
						$translation->set('auto', true);
					}

					if ($translationTable->save($translation)) {
						$translated++;
					} else {
						$failed++;
					}
				}
			}
		}

		if ($translated > 0) {
			$this->Flash->success(__d('translate', '{0} translation(s) created successfully.', $translated));
		}
		if ($failed > 0) {
			$this->Flash->warning(__d('translate', '{0} translation(s) could not be created.', $failed));
		}

		return $this->redirect(['action' => 'entries', $tableName]);
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
			} catch (Exception $e) {
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
	 * Batch action dispatcher - routes to appropriate batch method
	 *
	 * @param string $tableName Translation table name
	 * @return \Cake\Http\Response|null
	 */
	public function batch(string $tableName) {
		$this->request->allowMethod(['post']);

		$batchAction = $this->request->getData('batch_action', 'autoTranslate');

		if ($batchAction === 'batchUpdateAuto') {
			return $this->batchUpdateAuto($tableName);
		}

		return $this->autoTranslate($tableName);
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

		/** @var array<\Cake\ORM\Entity> $entries Entities from the query */
		$entries = $query->toArray();
		$translatedFromMemory = 0;
		$translatedFromApi = 0;
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

			// Translate using memory first, then API
			/** @var string|null $locale */
			$locale = $entry->get('locale');
			if (!$locale) {
				$failed++;

				continue;
			}

			$result = $service->translateWithMemory($sourceText, $locale, (string)$sourceLocale);

			if ($result['translation']) {
				$entry->set('content', $result['translation']);
				if ($hasAutoField) {
					// Memory translations are considered more reliable, but still mark as auto
					$entry->set('auto', true);
				}

				if ($table->save($entry)) {
					if ($result['source'] === 'memory') {
						$translatedFromMemory++;
					} else {
						$translatedFromApi++;
					}
				} else {
					$failed++;
				}
			} else {
				$failed++;
			}
		}

		$translated = $translatedFromMemory + $translatedFromApi;
		if ($translated > 0) {
			$message = __d('translate', '{0} entries translated successfully.', $translated);
			if ($translatedFromMemory > 0) {
				$message .= ' ' . __d('translate', '({0} from memory, {1} from API)', $translatedFromMemory, $translatedFromApi);
			}
			$this->Flash->success($message);
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
	protected function getTranslationTable(string $tableName): Table {
		$alias = Inflector::camelize($tableName);

		// Clear from registry if already registered to allow reconfiguration
		$locator = $this->getTableLocator();
		if ($locator->exists($alias)) {
			$locator->remove($alias);
		}

		return $this->fetchTable($alias, [
			'className' => Table::class,
			'table' => $tableName,
		]);
	}

	/**
	 * Sync missing translation entries for a table
	 *
	 * Creates empty translation entries for all base table records that don't have translations yet.
	 *
	 * @param string $tableName Translation table name
	 * @return \Cake\Http\Response|null
	 */
	public function sync(string $tableName) {
		$this->request->allowMethod(['post']);

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
		if (!in_array($baseTableName, $schemaCollection->listTables(), true)) {
			$this->Flash->error(__d('translate', 'Base table not found.'));

			return $this->redirect(['action' => 'index']);
		}

		$schema = $schemaCollection->describe($tableName);
		$strategy = $this->detectTranslationStrategy($schema);

		// Get target locales from request or use configured ones
		$targetLocales = $this->request->getData('locales', []);
		if (empty($targetLocales)) {
			$targetLocales = $this->getConfiguredLocales();
		}

		if (empty($targetLocales)) {
			$this->Flash->warning(__d('translate', 'No target locales configured. Please specify locales to sync.'));

			return $this->redirect(['action' => 'index']);
		}

		$translatedFields = $this->getTranslatedFieldsFromSchema($schema, $strategy);
		$hasAutoField = $schema->hasColumn('auto');

		// Get all base table IDs
		$baseTable = $this->fetchTable(Inflector::camelize($baseTableName));
		$baseIds = $baseTable->find()->select(['id'])->all()->extract('id')->toArray();

		// Get existing translation entries
		$translationTable = $this->getTranslationTable($tableName);

		$created = 0;
		foreach ($targetLocales as $locale) {
			// Find which IDs are missing for this locale
			$existingIds = $translationTable->find()
				->select(['id'])
				->where(['locale' => $locale])
				->all()
				->extract('id')
				->toArray();

			$missingIds = array_diff($baseIds, $existingIds);

			foreach ($missingIds as $id) {
				$data = [
					'id' => $id,
					'locale' => $locale,
				];

				// Initialize translated fields as null
				foreach ($translatedFields as $field) {
					$data[$field] = null;
				}

				if ($hasAutoField) {
					$data['auto'] = true;
				}

				$entry = $translationTable->newEntity($data);
				if ($translationTable->save($entry)) {
					$created++;
				}
			}
		}

		if ($created > 0) {
			$this->Flash->success(__d('translate', '{0} translation entries created.', $created));
		} else {
			$this->Flash->info(__d('translate', 'All entries are already synced.'));
		}

		return $this->redirect(['action' => 'entries', $tableName]);
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

			// Count base table records
			$baseCount = 0;
			$baseExists = in_array($baseTableName, $allTables, true);
			if ($baseExists) {
				$baseCount = $connection->execute("SELECT COUNT(*) as count FROM `{$baseTableName}`")->fetch('assoc')['count'] ?? 0;
			}

			// Get unique locales for this table
			$localesResult = $connection->execute("SELECT DISTINCT locale FROM `{$tableName}`")->fetchAll('assoc');
			$tableLocales = array_column($localesResult, 'locale');
			$localeCount = count($tableLocales);

			// Calculate potential total (base records * configured locales)
			$configuredLocales = $this->getConfiguredLocales();
			$potentialTotal = (int)$baseCount * max(count($configuredLocales), $localeCount, 1);

			$translationTables[$tableName] = [
				'name' => $tableName,
				'base_table' => $baseTableName,
				'base_exists' => $baseExists,
				'base_count' => (int)$baseCount,
				'row_count' => (int)$rowCount,
				'potential_total' => $potentialTotal,
				'locales' => $tableLocales,
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
	protected function detectTranslationStrategy(TableSchemaInterface $schema): string {
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
	protected function getForeignKeyColumn(TableSchemaInterface $schema): string {
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
	protected function getTranslatedFieldsFromSchema(TableSchemaInterface $schema, string $strategy): array {
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
		} catch (Exception $e) {
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
			$words = array_filter($words, fn ($w): bool => strlen((string)$w) > 2);

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
				if (!$term instanceof EntityInterface) {
					continue;
				}
				/** @var \Cake\Datasource\EntityInterface|null $translateString */
				$translateString = $term->get('translate_string');
				if (!$translateString instanceof EntityInterface) {
					continue;
				}
				$suggestions[] = [
					'source' => (string)$translateString->get('name'),
					'translation' => (string)$term->get('content'),
				];
			}
		} catch (Exception $e) {
			// Translate tables might not exist
		}

		return $suggestions;
	}

	/**
	 * Get configured locales from app configuration
	 *
	 * Checks the following configuration keys in order:
	 * 1. Translate.locales - plugin-specific config
	 * 2. I18n.supportedLocales - CakePHP I18n config
	 * 3. App.supportedLocales - common app config
	 *
	 * For associative arrays (locale => label), only the keys are returned.
	 *
	 * @return array<string>
	 */
	protected function getConfiguredLocales(): array {
		$locales = [];

		// Try Translate.locales first (plugin-specific)
		$supported = Configure::read('Translate.locales');
		if (is_array($supported) && !empty($supported)) {
			$locales = is_string(array_key_first($supported)) ? array_keys($supported) : array_values($supported);
		}

		// Try I18n.supportedLocales
		if (empty($locales)) {
			$supported = Configure::read('I18n.supportedLocales');
			if (is_array($supported)) {
				$locales = is_string(array_key_first($supported)) ? array_keys($supported) : array_values($supported);
			}
		}

		// Also check App.supportedLocales
		if (empty($locales)) {
			$supported = Configure::read('App.supportedLocales');
			if (is_array($supported)) {
				$locales = is_string(array_key_first($supported)) ? array_keys($supported) : array_values($supported);
			}
		}

		// Add default locale if not already included
		$defaultLocale = Configure::read('App.defaultLocale') ?? Configure::read('I18n.defaultLocale') ?? 'en_US';
		if (!in_array($defaultLocale, $locales, true)) {
			$locales[] = $defaultLocale;
		}

		return $locales;
	}

	/**
	 * Guess the display field from table schema
	 *
	 * @param \Cake\Database\Schema\TableSchemaInterface $schema Table schema
	 * @return string|null
	 */
	protected function guessDisplayField(TableSchemaInterface $schema): ?string {
		$columns = $schema->columns();
		$candidates = ['name', 'title', 'label', 'slug', 'username', 'email'];

		foreach ($candidates as $candidate) {
			if (in_array($candidate, $columns, true)) {
				return $candidate;
			}
		}

		return null;
	}

}
