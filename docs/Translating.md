# Translating

## Getting Started

* Create project (for your main app).
* Create languages you want to support.
* Import POT files etc.
* Disable strings or locales you do not want to be translated.

You can start translating.


## Validation
By default, the translate view validates the input, e.g. matching placeholders.
If you do not want that in certain cases, deactivate it via checkbox.


## Working with HTML translations
The plugin tries to auto-detect HTML by `<` / `>` chars.
You can also manually declare them as such.

This means this content does not get any escaping - make sure all text is valid NOT HTML characters or manually escape it.

### Experimental
We are trying to provide a "autoParagraph" option that would treat the text as non HTML input, but would format it as HTML on output then.


## Enabling code excerpts

Code references resolve relative to the selected project's path. An empty project path uses the application directory. Keep references relative to that root, such as `src/Controller/PagesController.php:42`.

The path now belongs to the project. When upgrading from per-domain paths, set the project path explicitly; the released migration removes the old domain column without copying its values.

By clicking any reference in your translate view it will open a modal with the highlighted code piece:

![code-excerpt](code-excerpt.jpg)

### Editing source references
The admin interface can display references and offers source editing in development. Enable the relevant reference settings only for users who may inspect and edit application source.

## Public translation interface

The `/translate` interface is separate from the admin backend. Public projects can expose a collaborative translation form through your application’s authentication and authorization rules. Hidden or inactive projects require the configured admin gate. Only strings from the selected project may be edited. Set `Translate.showCodeReferences` to literal `true` to expose source excerpts through this interface.

The admin **Clear all translation data** operation affects all projects. The normal Reset selection applies to the selected project. Clearing catalog data does not reset database identity sequences.

## Export directory names

Both the backend and CLI dump catalogs into the stored locale directory, such as `resources/locales/de_DE/default.po`. Earlier backend exports used the ISO code (`de`). Review existing catalogs when adopting the corrected backend export: keep the directory that matches your application's selected locale, and remove obsolete exports deliberately.
