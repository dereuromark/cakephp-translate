# Configuration

Merge settings under `Translate` in `config/app.php` or `config/app_local.php`. The plugin does not automatically load `config/app.example.php`; that file lists the available settings and a sample admin gate.

```php
'Translate' => [
    'noComments' => true,
    'plurals' => 2,
    'pluralExpression' => 'n != 1',
],
```

| Setting | Purpose |
| --- | --- |
| `adminAccess` | Closure receiving the current request; literal `true` admits the caller to Admin controllers. Unset denies access, including in debug mode. |
| `adminBackUrl`, `adminBackLabel` | Optional link from the admin header back to your application. |
| `noComments` | Omit references, flags and comments when dumping PO files. |
| `plurals`, `pluralExpression` | Gettext plural count and expression written into exported catalogs. These must match the target language. |
| `engine` | Engine class or ordered list of classes implementing `EngineInterface`. |
| `defaultLocale` | Default locale used by translation forms. |
| `layout` | Override the backend layout; the built-in layout is `Translate.simple`. |
| `editor`, `showCodeReferences` | Enable reference editing or source excerpts where supported by the backend. |
| `flags` | Flag-image format used by the language switcher. |
| `iconMap` | Admin icon-name mapping merged into `Icon.map`. |
| `shadowTableSuffix` | Suffix used when generating TranslateBehavior shadow tables; defaults to `_translations`. |
| `locales` | Locale choices for the TranslateBehavior interface; null enables detection. |
| `disableAuditLog` | Disable optional AuditStash logging on translation tables. |

The schema stores `content` and `plural_2`. Languages needing additional plural forms require an application migration adding `plural_3` through the number configured in `Translate.plurals`, up to six. Change the expression alongside the count and verify the generated PO headers.

## Optional integrations

Queue can process large imports in the background. Data provides an optional Languages association when that plugin is loaded. The legacy `languagesTable` and `usersTable` example keys are not read by the current plugin. AuditStash records translation changes when installed and enabled. BootstrapUI supplies an alternative form helper when installed; the built-in layout loads its CSS and JavaScript from CDNs. See [Layout](../contribute/layout.md) for local assets and CSP considerations.

The plugin inherits the host application’s AppController. Its admin gate supplements application authentication and callbacks; it does not replace them.
