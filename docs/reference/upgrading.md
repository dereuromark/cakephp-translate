# Upgrading to CakePHP 6

The `cake6` development branch requires PHP 8.5 and CakePHP 6. Follow the [installation guide](../guide/installation.md) for Composer stability and compatible development dependencies. Tools is optional: load it to use its pagination element, or use the built-in pagination fallback.

Custom framework extensions must use the CakePHP 6 configuration, view and patchable-field property names. Custom commands implement `execute(): ?int` and use the inherited arguments and console properties. Core extraction methods and properties use their CakePHP 6 names. Controller action dependencies must be explicitly registered with `Cake\Container\ContainerInterface`; Translate registers its own extraction service.

For trusted HTML link and button content, replace `escapeTitle => false` with `escape => false` and `escapeAttributes => true`. Escape dynamic label text with `h()`. For links using `escape => false` with configured string URLs, call `$this->Url->build($url)` before passing the result to `HtmlHelper::link()` so URL quotes and ampersands remain escaped.

BootstrapUI has no compatible upstream version available for this migration. Its optional form adapter is unverified and excluded from PHPStan until that dependency becomes available. The default forms work without it.

PHPStan uses CakePHP 6 native table generics and checked table lookup. The CakeDC extension is unavailable for CakePHP 6, so its custom finder named-argument checks do not run.

The test suite uses PHPUnit 13 and covers source extraction, POT regeneration and PO synchronization on CakePHP 6. The published documentation website covers stable CakePHP 5 releases; use this branch's documentation during development.
