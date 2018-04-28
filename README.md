# CakePHP Translate Plugin
[![Build Status](https://api.travis-ci.org/dereuromark/cakephp-translate.svg?branch=master)](https://travis-ci.org/dereuromark/cakephp-translate)
[![Coverage Status](https://img.shields.io/codecov/c/github/dereuromark/cakephp-translate/master.svg)](https://codecov.io/github/dereuromark/cakephp-translate?branch=master)
[![Latest Stable Version](https://poser.pugx.org/dereuromark/cakephp-translate/v/stable.svg)](https://packagist.org/packages/dereuromark/cakephp-translate)
[![Minimum PHP Version](http://img.shields.io/badge/php-%3E%3D%205.6-8892BF.svg)](https://php.net/)
[![License](https://poser.pugx.org/dereuromark/cakephp-translate/license.svg)](https://packagist.org/packages/dereuromark/cakephp-translate)
[![Total Downloads](https://poser.pugx.org/dereuromark/cakephp-translate/d/total.svg)](https://packagist.org/packages/dereuromark/cakephp-translate)
[![Coding Standards](https://img.shields.io/badge/cs-PSR--2--R-yellow.svg)](https://github.com/php-fig-rectified/fig-rectified-standards)

A CakePHP 3.x Plugin for managing translations DB driven.

Note: **Plugin in BETA version**

## Key features
- Import from POT, PO files or any service/API.
- Web-based and without external dependencies.
- Translate strings in all languages simultaneously.
- Allow others to help translating without having to know technical details.
- Auto-Translate and Auto-Suggest with Translate APIs (e.g. Google Translate PHP/JS, Yandex, ...) for efficiency.

## Benefits over normal PO editing
- Prevent duplicates, missing translations, collisions.
- Auto-Features like `trim()`, `h()`, newlines to `<p>/<br>`, espacing of `%s`.
- Validate placeholders (`{0}`, `%s`, ...).
- Preview and code excerpts of references.
- Auto-Add Controller names (singular + plural).
- Manage in Groups (=Domains) and export/enable/disable them.
- Creates clean PO files with all translations in usage to easier diff changes.

## Included translation services via APIs

- Google (free, limited)
- Yandex (free, limited)
- Transltr (free)

Add your translation engine here [in a heartbeat](docs#add-your-own-implementation).

## Installation
Including the plugin is pretty much as with every other CakePHP plugin:

```bash
composer require dereuromark/cakephp-translate
```

Then, to load the plugin either run the following command:

```sh
bin/cake plugin load Translate -b -r
# If you haven't loaded the Tools plugin already
bin/cake plugin load Tools -b -r
```

or manually add the following line to your app's `config/bootstrap.php` file:

```php
Plugin::load('Translate', ['bootstrap' => true, 'routes' => true]);
```

Routes are needed for the backed, the bootstrap sets up a few defaults.

Run this in console to create the necessary DB tables: 
```
bin/cake migrations migrate -p Translate
```

### Recommendations
Use `dereuromark/cakephp-queue` for larger projects to avoid timeout issues when importing PO files.


## Usage
Web Backend
- Navigate to `/admin/translate/` in your browser.

CLI
- Run `bin/cake translate`.

## Tips
- Use [TinyAuth](https://github.com/dereuromark/cakephp-tinyauth) or alike to manage access to the translation backend for user groups.
- Implement your own Translation engine if you want to have even better auto-suggest.

## Configuration and documentation
- [Documentation](docs)
