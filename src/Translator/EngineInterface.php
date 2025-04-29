<?php

namespace Translate\Translator;

interface EngineInterface {

	/**
	 * @param string $text Text
	 * @param string $to Iso2 code (e.g.: de)
	 * @param string $from Iso2 code (e.g.: en)
	 *
	 * @return string|null
	 */
	public function translate(string $text, string $to, string $from): ?string;

}
