# Getting started

Complete [installation](./installation.md) first. This example translates one application message without a translation API.

## Open the local backend

Merge a development gate into `config/app_local.php` in a local application:

```php
'Translate' => [
    'adminAccess' => static function (\Cake\Http\ServerRequest $request): bool {
        return \Cake\Core\Configure::read('debug') === true
            && in_array($request->clientIp(), ['127.0.0.1', '::1'], true);
    },
],
```

Open `/admin/translate`. Container setups may report a gateway IP and receive 403. Check the client address your application sees; do not enable remote access with a development gate. For deployed administration, check the authenticated identity and its permission to manage translations. Your application’s own AppController and authentication middleware also apply.

## Create a project and locale

Open **Projects** and add a project named `My application`, with type **CakePHP App**. Leave the path empty to use the application directory and mark it as the default project. Select that project in the backend.

Open **Locales** and add `German`, locale `de_DE`, ISO code `de`, with **Active** enabled. A configured Languages relation may replace the ISO field with a language selector.

## Add a message and extract it

Use CakePHP’s translation function in an application template:

```php
<?= h(__('Welcome')) ?>
```

Extract the application’s strings into POT files:

```sh
bin/cake i18n extract --paths src,templates --output resources/locales --merge no --extract-core no --overwrite
```

Open **Strings** and its **Extract** action at `/admin/translate/translate-strings/extract`. Select the available `default.pot` file and import it. The importer reads files from the selected project’s `resources/locales` directory.

## Translate and export

Find `Welcome` in **Strings**, open its translation form and enter `Willkommen` for German. Keep placeholder validation enabled when translating messages with placeholders.

Use **Dump** at `/admin/translate/translate-strings/dump` to export the active locale and domain. Review the generated `resources/locales/de_DE/default.po` file before committing it with your application.

To render German translations, set the locale before rendering the template:

```php
\Cake\I18n\I18n::setLocale('de_DE');
```

Clear the application’s translation cache after replacing catalogs. CakePHP’s [internationalization guide](https://book.cakephp.org/5/en/core-libraries/internationalization-and-localization.html) covers locale selection and cache configuration.

[Translation strategies](./strategies.md) explains database loading and direct extraction. [Translator engines](../TranslatorEngines.md) covers optional machine translation.
