# I18nEntries - TranslateBehavior CRUD

This feature adds a comprehensive CRUD interface for managing translations stored in CakePHP's TranslateBehavior tables.

## Overview

The I18nEntries controller provides:
- Overview of all translation tables in your database
- List, view, edit, and delete translation entries
- Batch auto-translation using configured translation engines
- Glossary suggestions from existing PO translations
- Support for both EAV and ShadowTable strategies

## Supported Table Strategies

### EAV Strategy (`*_i18n` tables)

Tables following the Entity-Attribute-Value pattern:
- `id` - Primary key
- `locale` - Language code (e.g., `de_CH`, `en_US`)
- `model` - Model name (optional)
- `foreign_key` - Reference to the source record
- `field` - Name of the translated field
- `content` - The translated text
- `auto` - Boolean flag for machine translations (optional)

Example table name: `articles_i18n`

### ShadowTable Strategy (`*_translations` tables)

Tables with direct field columns:
- `id` - Foreign key to source record
- `locale` - Language code
- Actual field columns (e.g., `name`, `description`, `title`)
- `auto` - Boolean flag for machine translations (optional)

Example table name: `articles_translations`

## Auto Field

The `auto` boolean field is used to track whether a translation was:
- **Machine-generated** (`auto = true`): Created by automatic translation
- **Manual** (`auto = false`): Created or edited by a human

### Benefits

1. **Safe re-translation**: Auto-translated entries can be safely overwritten when running batch translations
2. **Quality tracking**: Easy identification of entries that need human review
3. **Workflow support**: Filter to show only manual or only auto-translated entries

### Adding the Auto Field

When generating migrations via the TranslateBehavior generator, check the "Include auto field" option. Or add it manually:

```php
// In a migration
$table->addColumn('auto', 'boolean', [
    'default' => false,
    'null' => false,
    'comment' => 'True if translation was machine-generated',
])->update();
```

## Usage

### Routes

Add the following routes to access the I18nEntries controller:

```php
// In config/routes.php or your plugin routes
$routes->prefix('Admin', function (RouteBuilder $routes) {
    $routes->plugin('Translate', function (RouteBuilder $routes) {
        $routes->connect('/i18n-entries', ['controller' => 'I18nEntries', 'action' => 'index']);
        $routes->connect('/i18n-entries/:action/*', ['controller' => 'I18nEntries']);
    });
});
```

### Available Actions

| Action | URL | Description |
|--------|-----|-------------|
| index | `/admin/translate/i18n-entries` | Overview of all translation tables |
| entries | `/admin/translate/i18n-entries/entries/{tableName}` | List entries for a table |
| view | `/admin/translate/i18n-entries/view/{tableName}/{id}` | View single entry |
| edit | `/admin/translate/i18n-entries/edit/{tableName}/{id}` | Edit an entry |
| delete | `/admin/translate/i18n-entries/delete/{tableName}/{id}` | Delete an entry |
| autoTranslate | POST `/admin/translate/i18n-entries/auto-translate/{tableName}` | Batch translate entries |
| batchUpdateAuto | POST `/admin/translate/i18n-entries/batch-update-auto/{tableName}` | Mark entries as auto/manual |

### Filtering

The entries list supports filtering by:
- **Locale**: Filter by language
- **Field**: Filter by translated field (EAV only)
- **Auto/Manual**: Filter by translation type (if `auto` field exists)
- **Search**: Full-text search in content

### Auto-Translation

The auto-translate feature uses the configured translation engine (Google Translate, etc.) to translate entries. It:

1. Gets the source text from the base table
2. Translates to the target locale
3. Saves the translation with `auto = true`

Only entries with `auto = true` or empty content are translated to protect manual edits.

### Glossary Suggestions

When viewing or editing an entry, glossary suggestions are shown from existing PO translations. This helps maintain consistent terminology across your application.

## Configuration

The feature uses the standard Translate plugin configuration for translation engines:

```php
// In config/app.php or config/app_local.php
'Translate' => [
    'engine' => 'Google', // or 'Yandex', etc.
    'Google' => [
        'apiKey' => 'your-api-key',
    ],
],
```

## Security

- Table names are validated to prevent SQL injection
- Only tables ending with `_i18n` or `_translations` are accessible
- System tables (migrations, queues, etc.) are excluded
