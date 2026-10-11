# Installation

This development branch requires PHP 8.5 and CakePHP 6. The application must allow development stability for unreleased framework and companion packages. Keep stable packages preferred.

Install the plugin and migrations from your application directory:

```sh
composer config minimum-stability dev
composer config prefer-stable true
composer require dereuromark/cakephp-translate:dev-cake6 cakephp/migrations:dev-fix/6.x-cakephp-compatibility
```

Composer declares the supported PHP and CakePHP versions. For older applications, see the [version map](https://github.com/dereuromark/cakephp-translate/wiki#cakephp-version-map).

Load Translate with its bootstrap and routes:

```sh
bin/cake plugin load Translate
```

Load Migrations if your application has not already loaded it, then create the plugin tables:

```sh
bin/cake plugin load Migrations
bin/cake migrations migrate -p Translate
```

Translate does not require the Tools plugin. If your application loads Tools, the admin backend uses its pagination element; otherwise it uses the pagination element included with Translate.

Use `quoteIdentifiers => true` in the application datasource configuration: the schema contains names such as `default` and `primary`. Keep translation strings and contexts case-sensitive so messages that differ only in case remain distinct.

Continue with [Getting started](./getting-started.md). Loading the routes also exposes the public `/translate` interface. That interface permits public projects and requires the admin gate for private projects. The admin interface denies access until you configure its gate. To supply only custom routes, load the plugin with `routes => false` in application bootstrap and define the routes you need.
