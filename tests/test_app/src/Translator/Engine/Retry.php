<?php

namespace TestApp\Translator\Engine;

use Translate\Translator\EngineInterface;

class Retry implements EngineInterface {

	public static int $calls = 0;

	public function translate(string $text, string $to, string $from): ?string {
		static::$calls++;

		return static::$calls === 1 ? null : 'Recovered';
	}

}
