# Installation

Install the plugin and migrations from your application directory:

```sh
composer require dereuromark/cakephp-translate cakephp/migrations
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

The plugin’s entities use CakePHP Tools. Composer installs this dependency; loading Tools routes is unnecessary for the translation backend.

Use `quoteIdentifiers => true` in the application datasource configuration: the schema contains names such as `default` and `primary`. Keep translation strings and contexts case-sensitive so messages that differ only in case remain distinct.

Continue with [Getting started](./getting-started.md). Loading the routes also exposes the public `/translate` interface. That interface permits public projects and requires the admin gate for private projects. The admin interface denies access until you configure its gate. To supply only custom routes, load the plugin with `routes => false` in application bootstrap and define the routes you need.
