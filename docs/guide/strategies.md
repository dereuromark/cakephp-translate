# Translation strategies

## Exported gettext catalogs

Extract application messages into POT files, import them into the backend, edit translations and dump PO files. Commit the PO files with the application and review their changes before deployment. CakePHP loads those catalogs through its normal gettext loader.

The plugin stores projects, locales, domains, strings and translated terms in its database. Each project owns its domains and locales; terms connect a string with a locale. Machine-translation suggestions are stored separately in `translate_api_translations`.

## Database loading

To load a domain from the database, register a loader in application bootstrap:

```php
use Cake\I18n\I18n;
use Translate\I18n\DbMessagesLoader;

I18n::config('default', function ($domain, $locale) {
    return (new DbMessagesLoader($domain, $locale))();
});
```

Register each additional domain separately. The default loader uses the default application project; pass the named `projectId` constructor argument to select another project. Match the locale code to your stored locale.

The loader implements CakePHP’s package-loading API available from CakePHP 5.2.4. Use exported PO files on earlier supported CakePHP versions. Database changes must also reach your deployment environment; deploying source code alone does not deploy translations.

## Extract directly into the database

```sh
bin/cake i18n extract_to_db --paths src,templates --merge no --extract-core no
```

This discovers strings and stores them in the translation database. It does not create translated content. You can also use **Run Extract** in the backend and choose **Import directly to database**.

See [CLI commands](../reference/cli.md) for extraction, dumping and validation. [Getting started](./getting-started.md) walks through the exported-catalog workflow.
