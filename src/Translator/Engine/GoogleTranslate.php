<?php
/**
 * GoogleTranslate.class.php
 *
 * Class to talk with Google Translator for free.
 *
 * @package PHP Google Translate Free;
 * @category Translation
 * @author Adrián Barrio Andrés
 * @author Paris N. Baltazar Salguero <sieg.sb@gmail.com>
 * @copyright 2016 Adrián Barrio Andrés
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License 3.0
 * @version 2.0
 * @link https://statickidz.com/
 */

namespace Translate\Translator\Engine;

use Exception;

class GoogleTranslate {

	/**
	 * Retrieves the translation of a text
	 *
	 * @param string $source
	 *            Original language of the text on notation xx. For example: es, en, it, fr...
	 * @param string $target
	 *            Language to which you want to translate the text in format xx. For example: es, en, it, fr...
	 * @param string $text
	 *            Text that you want to translate
	 *
	 * @return string a simple string with the translation of the text in the target language
	 */
	public static function translate($source, $target, $text) {
		// Request translation
		$response = static::requestTranslation($source, $target, $text);
		// Get translation text
		// $response = self::getStringBetween("onmouseout=\"this.style.backgroundColor='#fff'\">", "</span></div>", strval($response));
		// Clean translation
		$translation = static::getSentencesFromJSON($response);

		return $translation;
	}

	/**
	 * Internal function to make the request to the translator service
	 *
	 * @internal
	 *
	 * @param string $source
	 *            Original language taken from the 'translate' function
	 * @param string $target
	 *            Target language taken from the ' translate' function
	 * @param string $text
	 *            Text to translate taken from the 'translate' function
	 *
	 * @return string The response of the translation service in JSON format
	 */
	protected static function requestTranslation($source, $target, $text) {
		// Google translate URL
		$url = 'https://translate.google.com/translate_a/single?client=at&dt=t&dt=ld&dt=qca&dt=rm&dt=bd&dj=1&hl=es-ES&ie=UTF-8&oe=UTF-8&inputm=2&otf=2&iid=1dd3b944-fa62-4b55-b330-74909a99969e';
		$fields = [
			'sl' => urlencode($source),
			'tl' => urlencode($target),
			'q' => urlencode($text),
		];
		if (strlen($fields['q']) >= 5000) {
			throw new Exception('Maximum number of characters exceeded: 5000');
		}

		// URL-ify the data for the POST
		$fieldsString = '';
		foreach ($fields as $key => $value) {
			$fieldsString .= $key . '=' . $value . '&';
		}
		$fieldsString = rtrim($fieldsString, '&');
		// Open connection
		$ch = curl_init();
		// Set the url, number of POST vars, POST data
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, (bool)count($fields));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fieldsString);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_USERAGENT, 'AndroidTranslate/5.3.0.RC02.130475354-53000263 5.1 phone TRANSLATE_OPM5_TEST_1');
		// Execute post
		$result = curl_exec($ch);
		// Close connection
		curl_close($ch);

		return (string)$result;
	}

	/**
	 * Dump of the JSON's response in an array
	 *
	 * @param string $json
	 *            The JSON object returned by the request function
	 *
	 * @return string A single string with the translation
	 */
	protected static function getSentencesFromJSON($json) {
		$sentencesArray = json_decode($json, true);
		$sentences = '';
		foreach ($sentencesArray['sentences'] as $s) {
			$sentences .= $s['trans'] ?? '';
		}

		return $sentences;
	}

}
