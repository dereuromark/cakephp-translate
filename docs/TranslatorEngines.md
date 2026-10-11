# Translator engines

Machine translation is optional. You can import, edit and export catalogs without calling a remote API.

Configure `Translate.engine` with a class implementing `EngineInterface`, or an ordered array of classes. `translate()` uses the first non-null result; `suggest()` collects results from all configured engines. Successful results are cached in the database per engine, input text and language pair. Null results are retried on a later lookup. Changing provider configuration does not automatically refresh an existing cached suggestion.

## Included engines

| Engine | Integration |
| --- | --- |
| `Translate\Translator\Engine\Google` | Unofficial Google endpoint used by default. Availability and quotas are not guaranteed. |
| `Translate\Translator\Engine\GoogleTranslate` | Internal helper used by `Google`; this is not an `EngineInterface` class and cannot be configured as an engine. |
| `Translate\Translator\Engine\Yandex` | Legacy Yandex SDK integration using `Yandex.key`; requires `yandex/translate-api`. |
| `Translate\Translator\Engine\Transltr` | Legacy Transltr endpoint; service availability is not guaranteed. |

Do not rely on historical free-service quota claims. Verify current provider terms and configure an engine suitable for your deployment. Text submitted for machine translation is sent to that provider.

## Custom engine

Create `src/Translator/Engine/MyEngine.php`:

```php
namespace App\Translator\Engine;

use Translate\Translator\EngineInterface;

class MyEngine implements EngineInterface
{
    public function translate(string $text, string $to, string $from): ?string
    {
        // Replace this with your provider call and return its translated text.
        return null;
    }
}
```

Configure the class:

```php
'Translate' => [
    'engine' => \App\Translator\Engine\MyEngine::class,
],
```

Return null when no translation is available. Handle provider errors inside the engine and log useful diagnostics without exposing credentials.

The Google and Transltr providers require the PHP cURL extension. Requests have a 5-second connection timeout and a 15-second total timeout. The bundled `GoogleTranslate` helper retains its GPL license notice; see [its source](https://github.com/dereuromark/cakephp-translate/blob/master/src/Translator/Engine/GoogleTranslate.php).
