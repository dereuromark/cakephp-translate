# Backend layout

`Translate.simple` renders the translation backend. It loads Bootstrap, Font Awesome, jQuery and Flag Icons from external CDNs. Some resources have integrity attributes; the Flag Icons stylesheet does not. The layout also contains inline styles and scripts.

If your application’s Content Security Policy blocks those resources, provide local assets and a compatible layout. Copy `templates/layout/simple.php` from the plugin to `templates/plugin/Translate/layout/simple.php` in your application to override it. You can instead configure `Translate.layout` with your application layout.

The layout reads the request’s `cspNonce` attribute for supported script tags. A nonce alone does not permit external stylesheets or inline style blocks; adjust your policy and template together.

## View blocks

| Block | Content |
| --- | --- |
| `title` | Browser page title |
| `meta` | Additional metadata |
| `css` | Additional stylesheets |
| `content` | Main page content |
| `script` | Additional scripts |

```php
$this->assign('title', 'Translation overview');
$this->start('script');
?>
<script>console.log('Translation overview loaded');</script>
<?php $this->end(); ?>
```

BootstrapUI’s Form helper is selected when its class is available. Installing that package does not provide your CSP policy or replace the layout’s CDN resources.

## Language switcher and alternative views

`Translate.LanguageSwitcher` loads active locales in a cell and passes them to its template as `activeLanguages`. The built-in layout still calls the `Translate.language_switcher` element, which delegates to the cell. Existing element overrides remain supported.

Use the cell directly from a PHP template:

```php
<?= $this->cell('Translate.LanguageSwitcher')->render() ?>
```

It selects the public or admin project according to the request prefix. Pass a project ID as the first argument to select one explicitly:

```php
<?= $this->cell('Translate.LanguageSwitcher', [$projectId])->render() ?>
```

Override `templates/plugin/Translate/cell/LanguageSwitcher/display.php` in your application to change its markup. A cell created by a view inherits that view's class. For Twig or another view engine, use your adapter's cell rendering support and provide a matching cell template in the application's override directory, using the extension required by that adapter. The template receives `activeLanguages` (locale entities with `locale` and `iso2`) and `currentLocale`. The plugin ships PHP templates; installing and configuring a Twig adapter remains an application task.
