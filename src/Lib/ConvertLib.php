<?php

namespace Translate\Lib;

use Cake\View\Helper\TextHelper;
use Cake\View\View;

class ConvertLib {

	const EOL = PHP_EOL;

	/**
	 * Convert from text to PO file content
	 *
	 * Options:
	 * - quotes
	 * - newline
	 * - escape
	 *
	 * @param string $text Text
	 * @param array $options Settings
	 *
	 * @return string
	 */
	public function convert($text, array $options = []) {
		$options += [
			'escape' => true,
		];

		if ($options['escape']) {
			$text = h($text);
		}

		if ($options['escape'] && !empty($options['newline'])) {
			$text = $this->_autoParagraph($text);
		} else {
			$text = str_replace(["\r\n", "\r", "\n"], '\n', $text);
		}
		return $text;
	}

	/**
	 * @param string $text
	 *
	 * @return string
	 */
	public function reverse($text) {
		$text = str_replace('\n', static::EOL, $text);
		return $text;
	}

	/**
	 * @param string $text
	 *
	 * @return string
	 */
	protected function _autoParagraph($text) {
		if (!isset($this->Text)) {
			$this->Text = new TextHelper(new View());
		}

		return $this->Text->autoParagraph($text);
	}

}
