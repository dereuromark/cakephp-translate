# POT Updater Tool - Implementation Plan

## Overview

A standalone CLI tool that can verify and update POT files for CakePHP plugins, runnable directly from a plugin's root directory.

## Use Cases

1. **CI/CD Integration**: Run in GitHub Actions to fail builds if POT files are outdated
2. **Pre-commit Hook**: Warn developers before committing if translations need updating
3. **Release Preparation**: Ensure POT files are current before tagging a release
4. **Multi-plugin Monorepos**: Check all plugins in a workspace

## Requirements

### Minimum Dependencies
- PHP 8.1+
- CakePHP Core (for i18n extraction logic)
- This plugin (`dereuromark/cakephp-translate`) as require-dev

### Installation in Plugin

```json
{
    "require-dev": {
        "dereuromark/cakephp-translate": "^2.0"
    }
}
```

## Proposed Interface

### Basic Usage

```bash
# From plugin root directory
php vendor/dereuromark/cakephp-translate/resources/pot-updater.php

# With options
php vendor/dereuromark/cakephp-translate/resources/pot-updater.php --dry-run
php vendor/dereuromark/cakephp-translate/resources/pot-updater.php --verbose
php vendor/dereuromark/cakephp-translate/resources/pot-updater.php --update
```

### Alternative: Composer Script

```json
{
    "scripts": {
        "pot-check": "php vendor/dereuromark/cakephp-translate/resources/pot-updater.php --dry-run",
        "pot-update": "php vendor/dereuromark/cakephp-translate/resources/pot-updater.php --update"
    }
}
```

## CLI Options

| Option | Description |
|--------|-------------|
| `--dry-run` | Compare and report differences without writing (default) |
| `--update` | Actually update the POT file(s) |
| `--verbose` / `-v` | Show detailed output including all strings |
| `--quiet` / `-q` | Only output errors, suitable for CI |
| `--path=<path>` | Custom path to scan (default: `src/`, `templates/`) |
| `--output=<path>` | Custom output path (default: `resources/locales/`) |
| `--domain=<name>` | Expected domain name (default: auto-detect from plugin name) |
| `--fail-on-diff` | Exit with code 1 if differences found (for CI) |
| `--ignore-comments` | Ignore comment/reference differences |
| `--json` | Output results as JSON |

## Output Examples

### Dry Run (Default)

```
POT Updater - Checking plugin: QueueScheduler
============================================

Scanning paths:
  - src/
  - templates/

Expected domain: queue_scheduler
POT file: resources/locales/queue_scheduler.pot

Results:
  Existing strings: 45
  Current strings:  48

  + 3 new strings found:
    - "Schedule deleted successfully"
    - "Invalid cron expression"
    - "Next run: {0}"

  - 0 strings removed
  ~ 2 strings with updated references

Status: OUT OF DATE
Run with --update to regenerate POT file.
```

### Update Mode

```
POT Updater - Updating plugin: QueueScheduler
=============================================

Scanning paths:
  - src/
  - templates/

Writing: resources/locales/queue_scheduler.pot
  - 48 strings extracted
  - 3 new strings added
  - 2 references updated

Status: UPDATED
```

### CI Mode (--quiet --fail-on-diff)

```
POT file outdated: 3 new, 0 removed, 2 changed
```
Exit code: 1

## Implementation Approach

### Option A: Token-based PHP Script (Implemented)

**Pros:**
- No CakePHP bootstrap needed for basic operation
- Fast execution (~50ms)
- Uses PHP's `token_get_all()` for reliable parsing (same approach as CakePHP core)
- Handles concatenated strings, escape sequences, heredocs properly

**Cons:**
- Maintained separately from CakePHP core (but uses same tokenization approach)

**File:** `scripts/pot-updater.php`

```php
#!/usr/bin/env php
<?php
/**
 * POT Updater - Standalone tool for checking/updating POT files
 *
 * Usage: php pot-updater.php [options]
 */

require_once dirname(__DIR__) . '/src/Utility/PotUpdater.php';

use Translate\Utility\PotUpdater;

$updater = new PotUpdater(getcwd());
exit($updater->run($argv));
```

### Option B: CakePHP Command (Alternative)

**Pros:**
- Full access to CakePHP's I18nExtractCommand
- Consistent behavior with `bin/cake i18n extract`

**Cons:**
- Requires CakePHP bootstrap
- Heavier, slower startup
- Plugin must be loaded in an app context

### Option C: Hybrid Approach (Best of Both)

Use standalone script that can optionally leverage CakePHP if available:

1. Try to use CakePHP's extraction if bootstrap available
2. Fall back to standalone parser if not
3. Compare results using our own POT parser

## Core Components

### 1. PotUpdater Utility Class

```php
namespace Translate\Utility;

class PotUpdater {

    protected string $pluginPath;
    protected array $options = [];

    public function __construct(string $pluginPath) {
        $this->pluginPath = $pluginPath;
    }

    /**
     * Main entry point
     */
    public function run(array $argv): int {
        $this->parseOptions($argv);

        $domain = $this->detectDomain();
        $existingStrings = $this->parseExistingPot($domain);
        $currentStrings = $this->extractStrings();

        $diff = $this->compareStrings($existingStrings, $currentStrings);

        if ($this->options['update']) {
            return $this->writePot($domain, $currentStrings);
        }

        return $this->reportDiff($diff);
    }

    /**
     * Detect plugin/domain name from composer.json or directory
     */
    protected function detectDomain(): string { ... }

    /**
     * Parse existing POT file
     */
    protected function parseExistingPot(string $domain): array { ... }

    /**
     * Extract strings from source files
     */
    protected function extractStrings(): array { ... }

    /**
     * Compare and generate diff
     */
    protected function compareStrings(array $existing, array $current): array { ... }
}
```

### 2. Standalone String Extractor

Reuse/adapt existing logic from CakePHP's I18nExtractCommand:

```php
namespace Translate\Utility;

class StringExtractor {

    /**
     * Patterns to match translation function calls
     */
    protected array $patterns = [
        '/__\(\s*[\'"](.+?)[\'"]\s*[,\)]/',
        '/__d\(\s*[\'"](.+?)[\'"]\s*,\s*[\'"](.+?)[\'"]\s*[,\)]/',
        '/__n\(\s*[\'"](.+?)[\'"]\s*,\s*[\'"](.+?)[\'"]\s*,/',
        '/__x\(\s*[\'"](.+?)[\'"]\s*,\s*[\'"](.+?)[\'"]\s*[,\)]/',
        // ... more patterns
    ];

    public function extract(string $path): array { ... }
}
```

### 3. POT File Comparison

```php
namespace Translate\Utility;

class PotComparator {

    public function compare(array $existing, array $current): array {
        return [
            'added' => array_diff_key($current, $existing),
            'removed' => array_diff_key($existing, $current),
            'changed' => $this->findChangedReferences($existing, $current),
            'unchanged' => array_intersect_key($existing, $current),
        ];
    }
}
```

## File Structure

```
src/
  PotUpdater/
    PotUpdater.php            # Main orchestrator
    CakeStringExtractor.php   # Wraps CakePHP's I18nExtractCommand
    PotParser.php             # Parse existing POT files
    PotComparator.php         # Compare POT contents
    PotWriter.php             # Write POT files

scripts/
    pot-updater.php           # CLI entry point
```

## GitHub Actions Integration Example

```yaml
name: Check Translations

on: [push, pull_request]

jobs:
  pot-check:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'

      - name: Install dependencies
        run: composer install --no-progress

      - name: Check POT files
        run: php vendor/dereuromark/cakephp-translate/resources/pot-updater.php --dry-run --fail-on-diff
```

## Edge Cases to Handle

1. **No existing POT file**: Create new one or warn?
2. **Multiple domains**: Plugin might have multiple POT files
3. **Vendor strings**: Ignore strings from vendor directory
4. **Context strings**: Handle `__x()` context properly
5. **Plural strings**: Handle `__n()` and `__dn()` properly
6. **Multiline strings**: Strings spanning multiple lines
7. **Concatenated strings**: `__('foo' . 'bar')` - warn about these
8. **Dynamic strings**: `__($var)` - warn about these

## Future Enhancements

1. **Watch mode**: Monitor files and update on change
2. **PO file sync**: Check if PO files have all strings from POT
3. **Translation coverage**: Report % of translated strings per locale
4. **Fuzzy detection**: Mark potentially outdated translations as fuzzy
5. **Auto-translate**: Integration with translation APIs for initial drafts

## Questions to Resolve

1. Should this support non-plugin projects (full CakePHP apps)?
2. Should we support `.ctp` files or only `.php`?
3. How to handle plugins with non-standard directory structures?
4. Should we validate the POT file format as well?
5. Config file support (`.pot-updater.json` or similar)?

## Next Steps

1. [x] Create basic `PotUpdater` utility class
2. [x] Implement standalone string extraction (or reuse CakePHP's)
3. [x] Implement POT comparison logic
4. [x] Create CLI entry point script
5. [x] Add tests with sample plugin structure
6. [ ] Document usage in README
7. [ ] Create GitHub Action example workflow
