# Contributing

Install the repository’s development dependencies:

```sh
composer install
```

Run checks from the plugin directory:

```sh
composer test
composer cs-check
composer stan-setup
composer stan
```

`composer cs-fix` applies coding-standard fixes. `composer test-coverage` produces coverage reports when a supported coverage driver is available. Database tests use `DB_URL`; the default test connection is SQLite.

Update the plugin’s POT template through a CakePHP application that loads Translate:

```sh
bin/cake i18n extract --plugin Translate --overwrite --merge no --extract-core no
```

## Documentation

```sh
cd docs
npm ci
npm run docs:dev
npm run docs:build
```

Check examples against the current source and confirm that links work in the built site. [Layout](./contribute/layout.md) describes the application backend layout.
