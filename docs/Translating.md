# Translating

## Getting Started

* Create project (for your main app).
* Create languages you want to support.
* Import POT files etc.
* Disable strings or groups you do not want to be translated.

You can start translating.


## Validation
By default the translate view validates the input, e.g. matching placeholders.
If you do not want that in certain cases, deactive it via checkbox.


## Working with HTML tanslations
The plugin tries to auto-detect HTML by `<` / `>` chars.
You can also manually declare them as such.

This means this content does not get any escaping - make sure all text is valid NOT HTML characters or manually escape it.

### Experimental
We are trying to provide a "autoParagraph" option that would treat the text as non HTML input, but would format it as HTML on output then.


## Enabling code excerpts

For displaying the referenced code pieces you need to set a relative path per TranslateGroup.
That is usually (per domain):
- default (app): `src/`
- cake (core): `vendor/cakephp/cakephp/src/`
- tools (plugin): `vendor/dereuromark/cakephp-tools/src/`

By clicking any reference in your translate view it will open a modal with the highlighted code piece:

![code-excerpt](code-excerpt.jpg)

### TODO
In the future we plan on allowing to jump right into code in development mode (from your browser to the IDE).

We also think about allowing to auto-fix any translating string in the source files, e.g. in case of a typo (also dev only).
A simple`preg_replace()` into the referenced files and the source string is corrected via one click.
