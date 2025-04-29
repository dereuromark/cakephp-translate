<?php

namespace Translate\Translator\Engine;

use Translate\Translator\EngineInterface;

class Google implements EngineInterface {

	/**
	 * @param string $text Text up to 5000 chars
	 * @param string $to Iso2 code (e.g.: de)
	 * @param string $from Iso2 code (e.g.: en)
	 *
	 * @return string|null
	 */
	public function translate(string $text, string $to, string $from): ?string {
		$result = GoogleTranslate::translate($from, $to, $text);
		if ($result === '') {
			return null;
		}

		return $result;
	}

}
