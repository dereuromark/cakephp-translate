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
