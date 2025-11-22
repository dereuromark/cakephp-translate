<?php

namespace Translate\PotUpdater;

/**
 * Writes POT file format from extracted strings.
 */
class PotWriter {

	/**
	 * Generate POT file content
	 *
	 * @param array<string, array{
	 *   msgid: string,
	 *   msgid_plural: string|null,
	 *   msgctxt: string|null,
	 *   references: array<string>,
	 *   comments: array<string>
	 * }> $strings Extracted strings
	 * @param array<string, mixed> $options Options for header
	 * @return string
	 */
	public function generate(array $strings, array $options = []): string {
		$output = $this->generateHeader($options);

		// Sort strings by msgid for consistent output
		ksort($strings);

		foreach ($strings as $data) {
			$output .= $this->generateEntry($data);
		}

		return $output;
	}

	/**
	 * Generate POT header
	 *
	 * @param array<string, mixed> $options Header options
	 * @return string
	 */
	protected function generateHeader(array $options = []): string {
		$project = $options['project'] ?? '';
		$version = $options['version'] ?? '';
		$date = date('Y-m-d H:i+0000');

		$header = '# LANGUAGE translation of ' . ($project ?: 'CakePHP Application') . "\n";
		$header .= "# Copyright YEAR NAME <EMAIL@ADDRESS>\n";
		$header .= "#\n";
		$header .= "#, fuzzy\n";
		$header .= "msgid \"\"\n";
		$header .= "msgstr \"\"\n";
		$header .= "\"Project-Id-Version: {$project} {$version}\\n\"\n";
		$header .= "\"POT-Creation-Date: {$date}\\n\"\n";
		$header .= "\"PO-Revision-Date: YEAR-MO-DA HO:MI+ZONE\\n\"\n";
		$header .= "\"Last-Translator: NAME <EMAIL@ADDRESS>\\n\"\n";
		$header .= "\"Language-Team: LANGUAGE <EMAIL@ADDRESS>\\n\"\n";
		$header .= "\"MIME-Version: 1.0\\n\"\n";
		$header .= "\"Content-Type: text/plain; charset=UTF-8\\n\"\n";
		$header .= "\"Content-Transfer-Encoding: 8bit\\n\"\n";
		$header .= "\"Plural-Forms: nplurals=INTEGER; plural=EXPRESSION;\\n\"\n";
		$header .= "\n";

		return $header;
	}

	/**
	 * Generate a single POT entry
	 *
	 * @param array{
	 *   msgid: string,
	 *   msgid_plural: string|null,
	 *   msgctxt: string|null,
	 *   references: array<string>,
	 *   comments: array<string>
	 * } $data String data
	 * @return string
	 */
	protected function generateEntry(array $data): string {
		$output = '';

		// Add references as comments
		foreach ($data['references'] as $reference) {
			$output .= "#: {$reference}\n";
		}

		// Add any other comments
		foreach ($data['comments'] as $comment) {
			$output .= "#. {$comment}\n";
		}

		// Add context if present
		if ($data['msgctxt'] !== null) {
			$output .= 'msgctxt ' . $this->formatString($data['msgctxt']) . "\n";
		}

		// Add msgid
		$output .= 'msgid ' . $this->formatString($data['msgid']) . "\n";

		// Add plural form if present
		if ($data['msgid_plural'] !== null) {
			$output .= 'msgid_plural ' . $this->formatString($data['msgid_plural']) . "\n";
			$output .= "msgstr[0] \"\"\n";
			$output .= "msgstr[1] \"\"\n";
		} else {
			$output .= "msgstr \"\"\n";
		}

		$output .= "\n";

		return $output;
	}

	/**
	 * Format a string for POT file
	 *
	 * Handles multiline strings and escaping.
	 *
	 * @param string $string The string to format
	 * @return string
	 */
	protected function formatString(string $string): string {
		// Escape special characters
		$string = str_replace(['\\', '"', "\r"], ['\\\\', '\\"', ''], $string);

		// Check for newlines
		if (strpos($string, "\n") !== false) {
			// Multiline string
			$lines = explode("\n", $string);
			$output = "\"\"\n";
			foreach ($lines as $i => $line) {
				$suffix = $i < count($lines) - 1 ? '\\n' : '';
				$output .= '"' . $line . $suffix . "\"\n";
			}

			return rtrim($output, "\n");
		}

		return '"' . $string . '"';
	}

	/**
	 * Write POT content to file
	 *
	 * @param string $path File path
	 * @param array<string, array{
	 *   msgid: string,
	 *   msgid_plural: string|null,
	 *   msgctxt: string|null,
	 *   references: array<string>,
	 *   comments: array<string>
	 * }> $strings Extracted strings
	 * @param array<string, mixed> $options Options for header
	 * @return bool
	 */
	public function write(string $path, array $strings, array $options = []): bool {
		$content = $this->generate($strings, $options);
		$dir = dirname($path);

		if (!is_dir($dir)) {
			mkdir($dir, 0755, true);
		}

		return file_put_contents($path, $content) !== false;
	}

}
