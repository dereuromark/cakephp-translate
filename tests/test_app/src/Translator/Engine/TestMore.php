<?php

namespace App\Translator\Engine;

use Translate\Translator\EngineInterface;

class TestMore implements EngineInterface {

	/**
	 * @param string $text Text up to 5000 chars
	 * @param string $to Iso2 code (e.g.: de)
	 * @param string $from Iso2 code (e.g.: en)
	 *
	 * @return string|null
	 */
	public function translate($text, $to, $from) {
		if ($text === '') {
			return null;
		}
		$result = strtolower($text);

		return $result;
	}

}
