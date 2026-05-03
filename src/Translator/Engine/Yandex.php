<?php

namespace Translate\Translator\Engine;

use Cake\Core\Configure;
use Cake\Log\Log;
use Translate\Translator\EngineInterface;
use Yandex\Translate\Exception;
use Yandex\Translate\Translator;

class Yandex implements EngineInterface {

	/**
	 * @param string $text Text up to 5000 chars
	 * @param string $to Iso2 code (e.g.: de)
	 * @param string $from Iso2 code (e.g.: en)
	 *
	 * @return string|null
	 */
	public function translate(string $text, string $to, string $from): ?string {
		try {
			$translator = new Translator(Configure::read('Yandex.key')); // ['sslVerifyPeer' => false]
			/** @var \Yandex\Translate\Translation $translation */
			$translation = $translator->translate($text, $from . '-' . $to);

			$result = (string)$translation;
		} catch (Exception $e) {
			// Upstream API messages may include the API key or other diagnostics; surface details
			// only in debug mode and log a generic warning otherwise (Issue #9).
			if (Configure::read('debug')) {
				trigger_error($e->getMessage());
			} else {
				Log::warning('Yandex translation failed', ['exception' => $e]);
			}

			return null;
		}

		if ($result === '') {
			return null;
		}

		return $result;
	}

}
