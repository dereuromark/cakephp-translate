<?php

namespace Translate\Controller\Admin;

use Cake\Database\Schema\CollectionInterface;
use Cake\Database\Schema\TableSchemaInterface;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\Utility\Inflector;
use Translate\Controller\TranslateAppController;

/**
 * TranslateBehavior Usage Controller
 *
 * Shows where CakePHP's TranslateBehavior is used and helps generate shadow table migrations
 *
 * @property \Translate\Controller\Component\TranslationComponent $Translation
 */
class TranslateBehaviorController extends TranslateAppController {

	use LocatorAwareTrait;

	/**
	 * @var string|null
	 */
	protected ?string $defaultTable = null;

	/**
	 * Index - show TranslateBehavior usage across the application
	 *
	 * @return void
	 */
	public function index() {
		/** @var \Cake\Database\Connection $connection */
		$connection = ConnectionManager::get('default');
		$schemaCollection = $connection->getSchemaCollection();
		$allTables = $this->filterApplicationTables($schemaCollection->listTables());

		// Find all shadow tables and get their info
		[$shadowTables, $orphanedShadowTables] = $this->findShadowTables($allTables, $schemaCollection, $connection);

		// Scan for models using TranslateBehavior
		$modelsWithBehavior = $this->scanModelsForTranslateBehavior();

		// Find tables that could use TranslateBehavior
		$candidateTables = $this->findCandidateTables($allTables, $shadowTables, $schemaCollection);

		// Get translation strategies for existing shadow tables
		$translationStrategies = $this->getTranslationStrategies($shadowTables);

		$this->set(compact(
			'shadowTables',
			'orphanedShadowTables',
			'modelsWithBehavior',
			'candidateTables',
			'translationStrategies',
		));
	}

	/**
	 * Filter out system tables from table list
	 *
	 * @param array $tables All tables
	 * @param bool $excludeI18n Whether to exclude _i18n shadow tables
	 * @return array Filtered application tables
	 */
	protected function filterApplicationTables(array $tables, bool $excludeI18n = false): array {
		return array_filter($tables, function ($table) use ($excludeI18n) {
			// Prefixes for plugin/system tables
			$systemPrefixes = ['cake_migrations', 'cake_seeds', 'translate_', 'queue_', 'audit_'];

			// Suffixes for migration/seed tables
			$systemSuffixes = ['phinxlog'];

			// Check if it's a shadow table
			if (str_ends_with($table, '_i18n')) {
				$baseTableName = substr($table, 0, -5);

				// Check if base table is a system table (by prefix)
				foreach ($systemPrefixes as $prefix) {
					if (str_starts_with($baseTableName, $prefix)) {
						return false;
					}
				}

				// Check if base table is a system table (by suffix)
				foreach ($systemSuffixes as $suffix) {
					if (str_ends_with($baseTableName, $suffix)) {
						return false;
					}
				}

				// If excludeI18n is true, exclude all remaining shadow tables
				if ($excludeI18n) {
					return false;
				}
			}

			// Check regular table prefixes
			foreach ($systemPrefixes as $prefix) {
				if (str_starts_with($table, $prefix)) {
					return false;
				}
			}

			// Check regular table suffixes
			foreach ($systemSuffixes as $suffix) {
				if (str_ends_with($table, $suffix)) {
					return false;
				}
			}

			return true;
		});
	}

	/**
	 * Find all shadow tables in the database
	 *
	 * @param array $allTables All database tables
	 * @param \Cake\Database\Schema\CollectionInterface $schemaCollection Schema collection
	 * @param \Cake\Database\Connection $connection Database connection
	 * @return array [shadowTables, orphanedShadowTables]
	 */
	protected function findShadowTables(array $allTables, CollectionInterface $schemaCollection, $connection): array {
		$shadowTables = [];
		$orphanedShadowTables = [];

		foreach ($allTables as $tableName) {
			if (str_ends_with($tableName, '_i18n')) {
				$baseTableName = substr($tableName, 0, -5);
				$baseTableExists = in_array($baseTableName, $allTables);

				$shadowTables[$baseTableName] = [
					'shadow_table' => $tableName,
					'base_table' => $baseTableName,
					'exists' => $baseTableExists,
					'schema' => $schemaCollection->describe($tableName),
					'row_count' => $connection->execute("SELECT COUNT(*) as count FROM `{$tableName}`")->fetch('assoc')['count'] ?? 0,
				];

				if (!$baseTableExists) {
					$orphanedShadowTables[] = $tableName;
				}
			}
		}

		return [$shadowTables, $orphanedShadowTables];
	}

	/**
	 * Get translation strategies for shadow tables
	 *
	 * @param array $shadowTables Shadow tables data
	 * @return array Translation strategies indexed by base table name
	 */
	protected function getTranslationStrategies(array $shadowTables): array {
		$translationStrategies = [];

		foreach ($shadowTables as $baseTable => $info) {
			if ($info['exists']) {
				$translationStrategies[$baseTable] = $this->detectTranslationStrategy($info['schema']);
			}
		}

		return $translationStrategies;
	}

	/**
	 * View detailed information about a shadow table
	 *
	 * @param string|null $tableName Table name
	 * @return \Cake\Http\Response|null
	 */
	public function view(?string $tableName = null) {
		if (!$this->validateShadowTableName($tableName)) {
			$this->Flash->error('Invalid shadow table name');

			return $this->redirect(['action' => 'index']);
		}

		/** @var \Cake\Database\Connection $connection */
		$connection = ConnectionManager::get('default');
		$schemaCollection = $connection->getSchemaCollection();

		try {
			$schema = $schemaCollection->describe($tableName);
		} catch (\Exception $e) {
			$this->Flash->error('Shadow table not found');

			return $this->redirect(['action' => 'index']);
		}

		$baseTableName = substr($tableName, 0, -5);
		$strategy = $this->detectTranslationStrategy($schema);
		$sampleData = $this->getSampleData($connection, $tableName);
		$translatedFields = $this->getTranslatedFields($sampleData, $strategy);
		$locales = $this->getLocalesInUse($connection, $tableName, $strategy);
		$baseTableExists = in_array($baseTableName, $schemaCollection->listTables());
		$modelInfo = $baseTableExists ? $this->getModelInfo($baseTableName) : null;

		$this->set(compact(
			'tableName',
			'baseTableName',
			'schema',
			'strategy',
			'sampleData',
			'translatedFields',
			'locales',
			'baseTableExists',
			'modelInfo',
		));

		return null;
	}

	/**
	 * Validate shadow table name
	 *
	 * @param string|null $tableName Table name
	 * @return bool
	 */
	protected function validateShadowTableName(?string $tableName): bool {
		return $tableName && str_ends_with($tableName, '_i18n');
	}

	/**
	 * Get sample data from table
	 *
	 * @param \Cake\Database\Connection $connection Database connection
	 * @param string $tableName Table name
	 * @param int $limit Number of rows to fetch
	 * @return array
	 */
	protected function getSampleData($connection, string $tableName, int $limit = 20): array {
		return $connection
			->execute("SELECT * FROM `{$tableName}` LIMIT {$limit}")
			->fetchAll('assoc');
	}

	/**
	 * Get locales in use for a shadow table
	 *
	 * @param \Cake\Database\Connection $connection Database connection
	 * @param string $tableName Table name
	 * @param string $strategy Translation strategy
	 * @return array
	 */
	protected function getLocalesInUse($connection, string $tableName, string $strategy): array {
		if ($strategy !== 'eav') {
			return [];
		}

		$localesResult = $connection
			->execute("SELECT DISTINCT locale FROM `{$tableName}` ORDER BY locale")
			->fetchAll('assoc');

		return array_column($localesResult, 'locale');
	}

	/**
	 * Scan application models for TranslateBehavior usage
	 *
	 * @return array
	 */
	protected function scanModelsForTranslateBehavior(): array {
		$modelsWithBehavior = [];
		/** @var \Cake\Database\Connection $connection */
		$connection = ConnectionManager::get('default');
		$schemaCollection = $connection->getSchemaCollection();
		$allTables = $schemaCollection->listTables();

		// Get main app tables (excluding system/plugin tables and shadow tables)
		$appTables = $this->filterApplicationTables($allTables, true);

		foreach ($appTables as $tableName) {
			try {
				$table = $this->fetchTable(Inflector::camelize($tableName));

				if ($table->hasBehavior('Translate')) {
					$behavior = $table->behaviors()->get('Translate');
					$config = $behavior->getConfig();

					$modelsWithBehavior[$tableName] = [
						'table' => $tableName,
						'model' => get_class($table),
						'fields' => $config['fields'] ?? [],
						'strategy' => $config['strategy'] ?? 'eav',
						'strategyClass' => $config['strategyClass'] ?? null,
						'has_shadow_table' => in_array($tableName . '_i18n', $allTables),
					];
				}
			} catch (\Exception $e) {
				// Table class doesn't exist or can't be loaded, skip
				continue;
			}
		}

		return $modelsWithBehavior;
	}

	/**
	 * Find tables that could benefit from TranslateBehavior
	 *
	 * @param array $allTables All tables
	 * @param array $shadowTables Existing shadow tables
	 * @param \Cake\Database\Schema\CollectionInterface $schemaCollection Schema collection
	 * @return array
	 */
	protected function findCandidateTables(array $allTables, array $shadowTables, CollectionInterface $schemaCollection): array {
		$candidates = [];

		// Get main app tables (excluding system/plugin tables and shadow tables)
		$appTables = $this->filterApplicationTables($allTables, true);

		foreach ($appTables as $tableName) {
			// Skip if already has shadow table
			if (isset($shadowTables[$tableName])) {
				continue;
			}

			try {
				/** @var \Cake\Database\Schema\TableSchema $schema */
				$schema = $schemaCollection->describe($tableName);
				$textFields = [];

				foreach ($schema->columns() as $columnName) {
					$columnType = $schema->getColumnType($columnName);
					if (in_array($columnType, ['string', 'text'])) {
						// Skip common non-translatable fields
						if (!in_array($columnName, ['id', 'uuid', 'email', 'password', 'token', 'slug', 'created', 'modified', 'updated'])) {
							$textFields[] = [
								'name' => $columnName,
								'type' => $columnType,
							];
						}
					}
				}

				if (!empty($textFields)) {
					$candidates[$tableName] = [
						'table' => $tableName,
						'text_fields' => $textFields,
						'field_count' => count($textFields),
					];
				}
			} catch (\Exception $e) {
				continue;
			}
		}

		return $candidates;
	}

	/**
	 * Detect translation strategy from shadow table schema
	 *
	 * @param \Cake\Database\Schema\TableSchemaInterface $schema Schema
	 * @return string
	 */
	protected function detectTranslationStrategy(TableSchemaInterface $schema): string {
		$columns = $schema->columns();

		// EAV strategy has: id, locale, model, foreign_key, field, content
		if (in_array('field', $columns) && in_array('content', $columns)) {
			return 'eav';
		}

		// Shadow table strategy has: id, locale, and then the actual field columns
		return 'shadow_table';
	}

	/**
	 * Get translated fields from sample data
	 *
	 * @param array $sampleData Sample data
	 * @param string $strategy Translation strategy
	 * @return array
	 */
	protected function getTranslatedFields(array $sampleData, string $strategy): array {
		if (empty($sampleData)) {
			return [];
		}

		if ($strategy === 'eav') {
			// Get unique field names
			$fields = array_unique(array_column($sampleData, 'field'));

			return array_filter($fields);
		}

		// Shadow table strategy - all columns except id, locale, foreign_key
		$columns = array_keys($sampleData[0]);
		$fields = array_diff($columns, ['id', 'locale', 'foreign_key']);

		return array_values($fields);
	}

	/**
	 * Get model information
	 *
	 * @param string $tableName Table name
	 * @return array|null
	 */
	protected function getModelInfo(string $tableName): ?array {
		try {
			$table = $this->fetchTable(Inflector::camelize($tableName));

			return [
				'class' => get_class($table),
				'alias' => $table->getAlias(),
				'has_behavior' => $table->hasBehavior('Translate'),
			];
		} catch (\Exception $e) {
			return null;
		}
	}

	/**
	 * Generate migration for a table
	 *
	 * @param string|null $tableName Table name
	 * @return \Cake\Http\Response|null
	 */
	public function generate(?string $tableName = null) {
		/** @var \Cake\Database\Connection $connection */
		$connection = ConnectionManager::get('default');
		$schemaCollection = $connection->getSchemaCollection();

		if (!$tableName) {
			// Show list of tables
			$allTables = $schemaCollection->listTables();
			$availableTables = $this->filterApplicationTables($allTables, true);

			$this->set(compact('availableTables'));

			return null;
		}

		// Check if table exists
		$allTables = $schemaCollection->listTables();
		if (!in_array($tableName, $allTables)) {
			$this->Flash->error(__d('translate', 'Table {0} not found', $tableName));

			return $this->redirect(['action' => 'generate']);
		}

		// Get table schema
		/** @var \Cake\Database\Schema\TableSchema $schema */
		$schema = $schemaCollection->describe($tableName);

		// Get translatable fields
		$translatableFields = [];
		foreach ($schema->columns() as $columnName) {
			$columnType = $schema->getColumnType($columnName);
			$columnData = $schema->getColumn($columnName);

			if (in_array($columnType, ['string', 'text'])) {
				if (!in_array($columnName, ['id', 'uuid', 'email', 'password', 'token', 'slug', 'created', 'modified', 'updated'])) {
					$translatableFields[] = [
						'name' => $columnName,
						'type' => $columnType,
						'length' => $columnData['length'] ?? null,
						'null' => $columnData['null'] ?? true,
					];
				}
			}
		}

		$migrationCode = null;
		$migrationName = null;
		$selectedFields = [];
		$strategy = 'shadow_table';

		if ($this->request->is('post')) {
			$data = $this->request->getData();
			$selectedFields = $data['fields'] ?? [];
			$strategy = $data['strategy'] ?? 'eav';

			if (empty($selectedFields)) {
				$this->Flash->warning(__d('translate', 'Please select at least one field to translate'));
			} else {
				$migrationCode = $this->generateMigrationCode($tableName, $selectedFields, $translatableFields, $strategy);
				$migrationName = 'AddI18nFor' . Inflector::camelize($tableName);
			}
		}

		$this->set(compact('tableName', 'translatableFields', 'migrationCode', 'migrationName', 'selectedFields', 'strategy'));

		return null;
	}

	/**
	 * Generate migration code
	 *
	 * @param string $tableName Table name
	 * @param array $selectedFields Selected field names
	 * @param array $allFields All translatable fields info
	 * @param string $strategy Translation strategy
	 * @return string
	 */
	protected function generateMigrationCode(string $tableName, array $selectedFields, array $allFields, string $strategy): string {
		$shadowTableName = $tableName . '_i18n';
		$className = 'AddI18nFor' . Inflector::camelize($tableName);

		if ($strategy === 'eav') {
			return $this->generateEavMigration($className, $shadowTableName, $tableName);
		}

		return $this->generateShadowTableMigration($className, $shadowTableName, $tableName, $selectedFields, $allFields);
	}

	/**
	 * Generate EAV strategy migration
	 *
	 * @param string $className Class name
	 * @param string $shadowTableName Shadow table name
	 * @param string $baseTableName Base table name
	 * @return string
	 */
	protected function generateEavMigration(string $className, string $shadowTableName, string $baseTableName): string {
		return <<<PHP
<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/**
 * Add i18n translation table for {$baseTableName} using EAV strategy
 */
class {$className} extends BaseMigration
{
    /**
     * Change Method.
     *
     * @return void
     */
    public function change(): void
    {
        \$table = \$this->table('{$shadowTableName}');

        \$table
            ->addColumn('locale', 'string', [
                'default' => null,
                'limit' => 6,
                'null' => false,
            ])
            ->addColumn('model', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('foreign_key', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('field', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('content', 'text', [
                'default' => null,
                'null' => true,
            ])
            ->addIndex(
                [
                    'locale',
                ],
            )
            ->addIndex(
                [
                    'model',
                    'foreign_key',
                    'locale',
                    'field',
                ],
                [
                    'name' => 'model_foreign_key_locale_field',
                    'unique' => true,
                ]
            )
            ->create();
    }
}
PHP;
	}

	/**
	 * Generate Shadow Table strategy migration
	 *
	 * @param string $className Class name
	 * @param string $shadowTableName Shadow table name
	 * @param string $baseTableName Base table name
	 * @param array $selectedFields Selected fields
	 * @param array $allFields All field info
	 * @return string
	 */
	protected function generateShadowTableMigration(string $className, string $shadowTableName, string $baseTableName, array $selectedFields, array $allFields): string {
		$fieldsMap = [];
		foreach ($allFields as $field) {
			$fieldsMap[$field['name']] = $field;
		}

		$columnDefinitions = [];
		foreach ($selectedFields as $fieldName) {
			if (!isset($fieldsMap[$fieldName])) {
				continue;
			}

			$field = $fieldsMap[$fieldName];
			$type = $field['type'];
			$length = $field['length'];
			$null = $field['null'] ? 'true' : 'false';

			$columnDef = "            ->addColumn('{$fieldName}', '{$type}', [\n";
			$columnDef .= "                'default' => null,\n";
			if ($length) {
				$columnDef .= "                'limit' => {$length},\n";
			}
			$columnDef .= "                'null' => {$null},\n";
			$columnDef .= '            ])';

			$columnDefinitions[] = $columnDef;
		}

		$columnsCode = implode("\n", $columnDefinitions);

		return <<<PHP
<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/**
 * Add i18n translation table for {$baseTableName} using Shadow Table strategy
 */
class {$className} extends BaseMigration
{
    /**
     * Change Method.
     *
     * @return void
     */
    public function change(): void
    {
        \$table = \$this->table('{$shadowTableName}');

        \$table
            ->addColumn('locale', 'string', [
                'default' => null,
                'limit' => 6,
                'null' => false,
            ])
            ->addColumn('foreign_key', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
{$columnsCode}
            ->addIndex(
                [
                    'locale',
                ],
            )
            ->addIndex(
                [
                    'foreign_key',
                    'locale',
                ],
                [
                    'name' => 'foreign_key_locale',
                    'unique' => true,
                ]
            )
            ->create();
    }
}
PHP;
	}

	/**
	 * Save migration file directly
	 *
	 * @return \Cake\Http\Response
	 */
	public function saveMigration() {
		$this->request->allowMethod(['post']);

		$tableName = $this->request->getData('table_name');
		$migrationCode = $this->request->getData('migration_code');
		$migrationName = $this->request->getData('migration_name');

		if (!$tableName || !$migrationCode || !$migrationName) {
			$this->Flash->error(__d('translate', 'Missing required data'));

			return $this->redirect(['action' => 'generate', $tableName]);
		}

		// Determine migration directory
		$migrationPath = ROOT . DS . 'config' . DS . 'Migrations';
		if (!is_dir($migrationPath)) {
			mkdir($migrationPath, 0755, true);
		}

		// Generate timestamped filename
		$timestamp = date('YmdHis');
		$filename = $timestamp . '_' . $migrationName . '.php';
		$filePath = $migrationPath . DS . $filename;

		// Check if file already exists
		if (file_exists($filePath)) {
			$this->Flash->error(__d('translate', 'Migration file already exists: {0}', $filename));

			return $this->redirect(['action' => 'generate', $tableName]);
		}

		// Save the file
		if (file_put_contents($filePath, $migrationCode) === false) {
			$this->Flash->error(__d('translate', 'Failed to write migration file'));

			return $this->redirect(['action' => 'generate', $tableName]);
		}

		$this->Flash->success(__d('translate', 'Migration file created successfully: {0}', $filename));

		return $this->redirect(['action' => 'generate', $tableName]);
	}

}
