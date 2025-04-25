<?php

namespace Translate\Translator\Engine;

use Translate\Translator\EngineInterface;
use Yandex\Translate\Exception;

class Transltr implements EngineInterface {

	/**
	 * @var string
	 */
	public const URL = 'http://transltr.org/api/translate?text=%s&to=%s&from=%s';

	/**
	 * @param string $text Text
	 * @param string $to Iso2 code (e.g.: de)
	 * @param string $from Iso2 code (e.g.: en)
	 *
	 * @return string|null
	 */
	public function translate($text, $to, $from) {
		$text = urlencode($text);
		/** @var non-empty-string $url */
		$url = sprintf(static::URL, $text, $to, $from);

		try {
			$handler = curl_init();
			curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);

			curl_setopt($handler, CURLOPT_URL, $url);
			curl_setopt($handler, CURLOPT_SSL_VERIFYPEER, false);

			/** @var string|false $remoteResult */
			$remoteResult = curl_exec($handler);
			if ($remoteResult === false) {
				throw new Exception(curl_error($handler), curl_errno($handler));
			}

			$response = json_decode($remoteResult, true);

			$result = $response['translationText'];
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
