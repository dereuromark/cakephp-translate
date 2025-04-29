<?php

namespace Translate\View\Helper;

use Cake\Core\Configure;
use Cake\View\Helper;
use RuntimeException;
use Translate\Model\Entity\TranslateDomain;

/**
 * @property \Cake\View\Helper\HtmlHelper $Html
 */
class TranslationHelper extends Helper {

	/**
	 * @var array
	 */
	protected array $helpers = ['Html'];

	/**
	 * Flag icon
	 *
	 * @param string|null $iso2Code identifierCode (iso2 right now: de, en, ...)
	 * @param array $attr for imageTag [optional]
	 * @param bool $checkExistence (defaults to FALSE)
	 * @return string imageTag or empty string on failure
	 */
	public function flag(?string $iso2Code = null, array $attr = [], bool $checkExistence = false) {
		if ($iso2Code) {
			if (Configure::read('Translate.flags') !== 'gif') {
				return '<i class="fi fi-' . strtolower($iso2Code) . '" title="' . strtolower($iso2Code) . '"></i>';
			}

			$icon = 'language_flags' . DS . $iso2Code . '.gif';
			if ($checkExistence === false || file_exists(WWW_ROOT . 'img' . DS . $icon)) {
				$options = ['alt' => strtoupper($iso2Code), 'title' => strtoupper($iso2Code), 'class' => 'languageFlag'];
				if ($attr) {
					$options = array_merge($options, $attr);
				}
				$icon = str_replace('\\', '/', $icon);

				return $this->Html->image('/translate/img/' . $icon, $options);
			}
		}

		return '';
	}

	/**
	 * Average over all languages
	 *
	 * @param array $coverage
	 *
	 * @return float
	 */
	public function totalCoverage($coverage) {
		$res = 0.0;
		if (empty($coverage)) {
			return $res;
		}
		foreach ($coverage as $c) {
			$res += $c;
		}
		$res = $res / count($coverage);

		return $res;
	}

	/**
	 * @var array
	 */
	public $start = ['r' => 255, 'g' => 0, 'b' => 0];

	/**
	 * @var array
	 */
	public $end = ['r' => 0, 'g' => 255, 'b' => 0];

	/**
	 * @param float|int $value (between $first, $second!)
	 * @param string $type hex, rgb, ... (defaults to hex) [optional]
	 * @return array<float>|string color
	 */
	public function getColor($value, $type = 'hex') {
		$color = $this->_calcColor($value);

		if ($type === 'rgb') {
			return $color;
		}
		if ($type === 'hex') {
			return $this->_rgb2hex($color);
		}

		throw new RuntimeException('Invalid type');
	}

	/**
	 * @see http://www.javascripter.net/faq/rgbtohex.htm
	 *
	 * @param array<float> $rgb
	 *
	 * @return string
	 */
	protected function _rgb2hex(array $rgb) {
		return sprintf('%02x%02x%02x', $rgb['r'], $rgb['g'], $rgb['b']);
	}

	/**
	 * @param float|int $value
	 *
	 * @return array<float>
	 */
	protected function _calcColor($value) {
		$color = [
			'r' => $this->start['r'] + (int)((($this->end['r'] - $this->start['r']) * $value) / 100),
			'g' => $this->start['g'] + (int)((($this->end['g'] - $this->start['g']) * $value) / 100),
			'b' => $this->start['b'] + (int)((($this->end['b'] - $this->start['b']) * $value) / 100),
		];

		return $color;
	}

	/**
	 * @param \Translate\Model\Entity\TranslateDomain $translateDomain
	 *
	 * @return bool
	 */
	public function canDisplayReference(TranslateDomain $translateDomain) {
		return (bool)$translateDomain->path;
	}

}
