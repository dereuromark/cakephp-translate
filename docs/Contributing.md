# Contributing

## Getting Started

* Make sure you have a [GitHub account](https://github.com/signup/free)
* Fork the repository on GitHub.

## Making Changes

I am looking forward to your contributions. There are several ways to help out:
* Write missing test cases
* Write patches for bugs/features, preferably with test cases included

How to work on the plugin:

- `composer update`

After installing all dependencies you are ready to develop on the plugin.

### Running tests
From with in the plugin:
- `composer test-setup`
- `composer test`
- `composer test-coverage`
- `composer cs-check`
- `composer cs-fix`
- `composer phpstan-setup`
- `composer phpstan`

### Updating POT translations file
This needs to be run from your application:
- `bin cake i18n extract --plugin Translate --overwrite --merge no --extract-core no`
