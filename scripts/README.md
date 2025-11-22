# Scripts

## POT Updater

Standalone tool for checking and updating POT files in CakePHP plugins.

### Installation

Add to your plugin's `composer.json`:

```json
{
    "require-dev": {
        "dereuromark/cakephp-translate": "^2.0"
    }
}
```

Then run:
```bash
composer update
```

### Usage

Run from your plugin's root directory:

```bash
# Check for differences (dry-run, default)
php vendor/dereuromark/cakephp-translate/scripts/pot-updater.php

# Update the POT file
php vendor/dereuromark/cakephp-translate/scripts/pot-updater.php --update

# CI mode: fail if POT is out of date
php vendor/dereuromark/cakephp-translate/scripts/pot-updater.php --fail-on-diff
```

### Composer Scripts

Add to your plugin's `composer.json` for convenience:

```json
{
    "scripts": {
        "pot-setup": "cp composer.json composer.backup && composer require --dev dereuromark/cakephp-translate && mv composer.backup composer.json",
        "pot-check": "php vendor/dereuromark/cakephp-translate/scripts/pot-updater.php --fail-on-diff",
        "pot-update": "php vendor/dereuromark/cakephp-translate/scripts/pot-updater.php --update"
    }
}
```

Then use:
```bash
composer pot-check   # Check if POT is up to date
composer pot-update  # Regenerate POT file
```

### Options

| Option | Description |
|--------|-------------|
| `--dry-run` | Compare and report differences without writing (default) |
| `--update` | Actually update the POT file(s) |
| `--verbose`, `-v` | Show detailed output including all strings |
| `--quiet`, `-q` | Only output errors, suitable for CI |
| `--path=<path>` | Custom paths to scan, comma-separated (default: `src,templates`) |
| `--output=<path>` | Custom output path (default: `resources/locales`) |
| `--domain=<name>` | Expected domain name (default: auto-detect from plugin name) |
| `--fail-on-diff` | Exit with code 1 if differences found (for CI) |
| `--ignore-references` | Ignore comment/reference differences |
| `--help`, `-h` | Show help message |

### Exit Codes

| Code | Description |
|------|-------------|
| 0 | Success (POT file is up to date or was updated successfully) |
| 1 | Differences found (when using `--fail-on-diff`) |
| 2 | Error (could not read/write files, invalid options) |

### CI Integration

#### GitHub Actions

```yaml
- name: Check POT file is up to date
  run: composer pot-check
```

#### GitLab CI

```yaml
pot-check:
  script:
    - composer pot-check
```
