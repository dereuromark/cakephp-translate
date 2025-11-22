<?php

namespace Translate\PotUpdater;

/**
 * Parses POT/PO file format into structured data.
 */
class PotParser {

	/**
	 * Parse a POT/PO file
	 *
	 * @param string $path Path to the file
	 * @return array<string, array{
	 *   msgid: string,
	 *   msgid_plural: string|null,
	 *   msgctxt: string|null,
	 *   references: array<string>,
	 *   comments: array<string>
	 * }>
	 */
	public function parse(string $path): array {
		if (!file_exists($path)) {
			return [];
		}

		$content = file_get_contents($path);
		if ($content === false) {
			return [];
		}

		return $this->parseContent($content);
	}

	/**
	 * Parse POT/PO content
	 *
	 * @param string $content File content
	 * @return array<string, array{
	 *   msgid: string,
	 *   msgid_plural: string|null,
	 *   msgctxt: string|null,
	 *   references: array<string>,
	 *   comments: array<string>
	 * }>
	 */
	public function parseContent(string $content): array {
		$entries = [];
		$blocks = $this->splitIntoBlocks($content);

		foreach ($blocks as $block) {
			$entry = $this->parseBlock($block);
			if ($entry !== null && $entry['msgid'] !== '') {
				// Create key from context + msgid
				$key = $entry['msgctxt'] ? $entry['msgctxt'] . "\x04" . $entry['msgid'] : $entry['msgid'];
				$entries[$key] = $entry;
			}
		}

		return $entries;
	}

	/**
	 * Split content into entry blocks
	 *
	 * @param string $content File content
	 * @return array<string>
	 */
	protected function splitIntoBlocks(string $content): array {
		// Normalize line endings
		$content = str_replace(["\r\n", "\r"], "\n", $content);

		// Split by double newlines
		$blocks = preg_split('/\n{2,}/', $content);

		return $blocks !== false ? array_filter($blocks) : [];
	}

	/**
	 * Parse a single entry block
	 *
	 * @param string $block Block content
	 * @return array{
	 *   msgid: string,
	 *   msgid_plural: string|null,
	 *   msgctxt: string|null,
	 *   references: array<string>,
	 *   comments: array<string>
	 * }|null
	 */
	protected function parseBlock(string $block): ?array {
		$lines = explode("\n", trim($block));

		$references = [];
		$comments = [];
		$msgctxt = null;
		$msgid = null;
		$msgidPlural = null;
		$currentField = null;
		$currentValue = '';

		foreach ($lines as $line) {
			$line = trim($line);

			// Reference comment
			if (str_starts_with($line, '#:')) {
				$ref = trim(substr($line, 2));
				// Split multiple references on the same line
				$refs = preg_split('/\s+/', $ref);
				if ($refs !== false) {
					$references = array_merge($references, array_filter($refs));
				}

				continue;
			}

			// Translator comment
			if (str_starts_with($line, '#.')) {
				$comments[] = trim(substr($line, 2));

				continue;
			}

			// Other comments (skip)
			if (str_starts_with($line, '#')) {
				continue;
			}

			// Field start
			if (preg_match('/^(msgctxt|msgid|msgid_plural|msgstr(?:\[\d+\])?)\s+/', $line, $match)) {
				// Save previous field
				if ($currentField !== null) {
					$this->assignField($currentField, $currentValue, $msgctxt, $msgid, $msgidPlural);
				}

				$currentField = $match[1];
				$currentValue = $this->extractQuotedString(substr($line, strlen($match[0])));

				continue;
			}

			// Continuation line (quoted string only)
			if (str_starts_with($line, '"') && $currentField !== null) {
				$currentValue .= $this->extractQuotedString($line);

				continue;
			}
		}

		// Save last field
		if ($currentField !== null) {
			$this->assignField($currentField, $currentValue, $msgctxt, $msgid, $msgidPlural);
		}

		if ($msgid === null) {
			return null;
		}

		return [
			'msgid' => $msgid,
			'msgid_plural' => $msgidPlural,
			'msgctxt' => $msgctxt,
			'references' => $references,
			'comments' => $comments,
		];
	}

	/**
	 * Assign a field value
	 *
	 * @param string $field Field name
	 * @param string $value Field value
	 * @param string|null $msgctxt Reference to msgctxt variable
	 * @param string|null $msgid Reference to msgid variable
	 * @param string|null $msgidPlural Reference to msgid_plural variable
	 * @return void
	 */
	protected function assignField(string $field, string $value, ?string &$msgctxt, ?string &$msgid, ?string &$msgidPlural): void {
		switch ($field) {
			case 'msgctxt':
				$msgctxt = $value;
				break;
			case 'msgid':
				$msgid = $value;
				break;
			case 'msgid_plural':
				$msgidPlural = $value;
				break;
			// msgstr fields are ignored for POT files
		}
	}

	/**
	 * Extract content from a quoted string
	 *
	 * @param string $line Line starting with quote
	 * @return string
	 */
	protected function extractQuotedString(string $line): string {
		$line = trim($line);

		if (!str_starts_with($line, '"')) {
			return '';
		}

		// Find matching end quote
		$result = '';
		$len = strlen($line);
		$i = 1; // Skip opening quote

		while ($i < $len) {
			$char = $line[$i];

			if ($char === '\\' && $i + 1 < $len) {
				// Escape sequence
				$next = $line[$i + 1];
				switch ($next) {
					case 'n':
						$result .= "\n";
						break;
					case 'r':
						$result .= "\r";
						break;
					case 't':
						$result .= "\t";
						break;
					case '"':
						$result .= '"';
						break;
					case '\\':
						$result .= '\\';
						break;
					default:
						$result .= $next;
				}
				$i += 2;

				continue;
			}

			if ($char === '"') {
				// End of string
				break;
			}

			$result .= $char;
			$i++;
		}

		return $result;
	}

}
