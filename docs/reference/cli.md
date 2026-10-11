# CLI commands

Loading Translate registers three commands. There is no generic `bin/cake translate` command.

## Extract into the database

```sh
bin/cake i18n extract_to_db --paths src,templates --merge no --extract-core no
```

The command extends CakePHP’s extractor and writes discovered strings into backend tables. It can prompt for missing extraction choices. Use `--help` for the options supported by your installed CakePHP version.

## Dump database translations

```sh
bin/cake i18n dump_from_db
```

The command writes PO files for active domains and the selected project’s locales, including inactive locales. Output directories use the stored locale code, such as `de_DE`. `--paths` accepts comma-separated output directories and `--plugin` selects a plugin project. Review generated files before committing them.

## Validate PO and POT files

```sh
bin/cake i18n validate --paths resources/locales --summary
bin/cake i18n validate --paths resources/locales --json
```

Validation checks placeholder differences, whitespace and HTML tags. `--key-based` skips HTML and whitespace checks when message IDs are translation keys. `--plugin` selects a plugin’s locales directory. Inspect the command’s exit status when using it in CI.

## Extract POT files without importing

CakePHP’s core extractor remains available:

```sh
bin/cake i18n extract --plugin Translate --overwrite --merge no --extract-core no
```

Use this to update the plugin’s own gettext template when contributing.
