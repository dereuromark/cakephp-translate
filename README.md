# CakePHP Translate Plugin
[![CI](https://github.com/dereuromark/cakephp-translate/actions/workflows/ci.yml/badge.svg?branch=master)](https://github.com/dereuromark/cakephp-translate/actions/workflows/ci.yml?query=branch%3Amaster)
[![Coverage Status](https://img.shields.io/codecov/c/github/dereuromark/cakephp-translate/master.svg)](https://codecov.io/github/dereuromark/cakephp-translate/branch/master)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%208-brightgreen.svg?style=flat)](https://phpstan.org/)
[![Latest Stable Version](https://poser.pugx.org/dereuromark/cakephp-translate/v/stable.svg)](https://packagist.org/packages/dereuromark/cakephp-translate)
[![Minimum PHP Version](http://img.shields.io/badge/php-%3E%3D%208.3-8892BF.svg)](https://php.net/)
[![License](https://poser.pugx.org/dereuromark/cakephp-translate/license.svg)](https://packagist.org/packages/dereuromark/cakephp-translate)
[![Total Downloads](https://poser.pugx.org/dereuromark/cakephp-translate/d/total.svg)](https://packagist.org/packages/dereuromark/cakephp-translate)
[![Coding Standards](https://img.shields.io/badge/cs-PSR--2--R-yellow.svg)](https://github.com/php-fig-rectified/fig-rectified-standards)

A CakePHP plugin for managing translations DB driven.

This branch is for use with **CakePHP 5.1+**. For details see [version map](https://github.com/dereuromark/cakephp-translate/wiki#cakephp-version-map).

## Key features
- Import from POT, PO files or any service/API.
- Web-based and without external dependencies.
- Translate strings in all languages simultaneously.
- Allow others to help translating without having to know technical details.
- Auto-Translate and Auto-Suggest with Translate APIs (e.g. Google Translate PHP/JS, Yandex, ...) for efficiency.
- Run i18n extract directly from web interface (with dry run support).
- Multi-project support with project-specific locale paths.
- Translation coverage dashboard with progress tracking.
- PO/POT file analyzer for detecting issues.

## Benefits over normal PO editing
- Prevent duplicates, missing translations, collisions.
- Auto-Features like `trim()`, `h()`, newlines to `<p>/<br>`, escaping of `%s`.
- Validate placeholders (`{0}`, `%s`, ...).
- Preview and code excerpts of references.
- Auto-Add Controller names (singular + plural).
- Manage in Domains and export/enable/disable them.
- Creates clean PO files with all translations in usage to easier diff changes.

## Included translation services via APIs

- Google (free, limited)
- Yandex (free, limited)
- Transltr (free)

Add your translation engine here [in a heartbeat](docs#add-your-own-implementation).

## Installation
Including the plugin is pretty much as with every other CakePHP plugin:

```bash
composer require dereuromark/cakephp-translate
```

Then, to load the plugin run the following command:

```sh
bin/cake plugin load Translate -b -r
# If you haven't loaded the Tools plugin already
bin/cake plugin load Tools -b -r
```

Routes are needed for the backed, the bootstrap sets up a few defaults.

Run this in console to create the necessary DB tables:
```
bin/cake migrations migrate -p Translate
```

### Recommendations
Use `dereuromark/cakephp-queue` for larger projects to avoid timeout issues when importing PO files.


## Usage
Web Backend
- Navigate to `/admin/translate/` in your browser.

CLI
- Run `bin/cake translate`.

### TranslateBehavior Support

This plugin now includes comprehensive support for CakePHP's built-in TranslateBehavior and shadow table (`_i18n`) management - all in one unified interface.

Navigate to `/admin/translate/translate-behavior` for:
- **View All Shadow Tables**: See all existing `_i18n` tables and their translation data
- **Detect Behavior Usage**: Automatically detect which models are using CakePHP's TranslateBehavior
- **Find Candidates**: Discover tables with translatable fields that don't yet have translation support
- **Generate Migrations**: Create shadow table migrations with just a few clicks

**Features**:
- Choose between **Shadow Table** (better performance, recommended) or **EAV** (more flexible) strategies
- Select which fields to translate from your existing tables
- Get complete, ready-to-use migration code with proper indexes and constraints
- Step-by-step instructions for adding the behavior to your Table class
- Automatic detection of translatable text fields
- Preview of shadow table schemas and translation data
- Shows which locales are in use
- Identifies orphaned shadow tables (shadow tables without base tables)

**Example workflow**:
1. Navigate to `/admin/translate/translate-behavior`
2. Click "Generate Migration" on a candidate table (e.g., `articles`)
3. Select fields to translate (e.g., `title`, `body`)
4. Choose strategy (Shadow Table is default and recommended)
5. Copy generated migration code
6. Save to `config/Migrations/AddI18nForArticles.php`
7. Run `bin/cake migrations migrate`
8. Add behavior to your ArticlesTable class

Accessible from the main dashboard under "Quick Actions".

### Web-based i18n Extract

Run CakePHP's i18n extract command directly from the web interface without needing CLI access.

Navigate to `/admin/translate/translate-strings/run-extract` for:
- **Dry Run Mode**: Preview extracted strings before writing files
- **Custom Paths**: Specify which directories to scan
- **Plugin Support**: Automatic domain detection for plugin-type projects
- **Live Output**: See extraction progress and results in real-time

**Features**:
- Extracts to temp directory first, then copies to final location (ensures consistent behavior)
- For plugins, automatically uses the plugin name as domain (e.g., `translate.pot` for Translate plugin)
- Filters out unwanted POT files (like `default.pot`) for plugin projects
- Shows string count and preview of generated POT files
- Supports merge and overwrite options

**Example workflow**:
1. Navigate to `/admin/translate/translate-strings/run-extract`
2. Configure paths to scan (defaults to `src/` and `templates/`)
3. Enable "Dry run" to preview first
4. Review the extracted strings
5. Disable "Dry run" and run again to write files
6. Import the generated POT file via the Extract/Import page

### PO File Analyzer

Analyze PO/POT files for common issues and inconsistencies.

Navigate to `/admin/translate/translate-strings/analyze` to:
- Upload or paste PO file content
- Select from existing project PO/POT files
- Detect formatting issues, missing translations, fuzzy entries
- Identify placeholder mismatches between source and translation

### Translation Coverage

The main dashboard shows translation coverage across all domains and locales:
- Progress bars for each language
- Counts of translated vs. total strings
- Quick links to untranslated strings
- Batch confirm functionality for reviewed translations

## Tips
- Use [TinyAuth](https://github.com/dereuromark/cakephp-tinyauth) or Cake Authentication plugin to manage access to the translation backend for user groups.
- Implement your own Translation engine if you want to have even better auto-suggest.

## Configuration and documentation
- [Documentation](docs)
