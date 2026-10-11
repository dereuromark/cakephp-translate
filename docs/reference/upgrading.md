# Upgrading to CakePHP 6

The `cake6` development branch requires PHP 8.5 and CakePHP 6. Follow the [installation guide](../guide/installation.md) for Composer stability and compatible development dependencies. Tools remains required for the entity base classes and enum support.

Custom framework extensions must use the CakePHP 6 configuration, view and patchable-field property names. Custom commands implement `execute(): ?int` and use the inherited arguments and console properties. Core extraction methods and properties use their CakePHP 6 names. Controller action dependencies must be explicitly registered with `Cake\Container\ContainerInterface`; Translate registers its own extraction service.

Custom link and button templates use `escape` for their HTML content. Attributes remain escaped by the framework. Existing application templates that pass `escapeTitle` need the same update.

BootstrapUI has no compatible upstream version available for this migration. Its optional form adapter is unverified and excluded from PHPStan until that dependency becomes available. The default forms work without it.

The test suite uses PHPUnit 13 and covers source extraction, POT regeneration and PO synchronization on CakePHP 6. The published documentation website covers stable CakePHP 5 releases; use this branch's documentation during development.
