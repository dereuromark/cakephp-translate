<?php
/**
 */

namespace Translate\Translator\Engine;

use Cake\Core\Configure;
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
	public function translate($text, $to, $from) {
		try {
			$translator = new Translator(Configure::read('Yandex.key'));
			$translation = $translator->translate($text, $from . '-' . $to);

			$result = (string)$translation;
		} catch (Exception $e) {
			trigger_error($e->getMessage());
			return null;
		}

		if ($result === '') {
			return null;
		}

		return $result;
	}

}
