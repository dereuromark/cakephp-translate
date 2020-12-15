<?php

namespace Translate\Parser;

use Exception;

/**
 * @link https://github.com/MAXakaWIZARD/PoParser
 */
class Writer {

	/**
	 * @param string $filePath
	 * @param array $entries
	 *
	 * @throws \Exception
	 * @return void
	 */
	public function write(string $filePath, array $entries): void {
		$handle = $this->openFile($filePath);

		$entriesCount = count($entries);
		$counter = 0;
		foreach ($entries as $entry) {
			$entryStr = $this->getEntryStr($entry, $counter, $entriesCount);
			fwrite($handle, $entryStr);

			$counter++;
		}

		fclose($handle);
	}

	/**
	 * @param string $filePath
	 * @throws \Exception
	 * @return resource
	 */
	protected function openFile($filePath) {
		if (empty($filePath)) {
			throw new Exception('Output file not defined.');
		}

		$handle = fopen($filePath, 'wb');
		if ($handle === false) {
			throw new Exception("Unable to open file for writing: {$filePath}");
		}

		return $handle;
	}

	/**
	 * @param array $entry
	 * @param int $index
	 * @param int $entriesCount
	 *
	 * @return string
	 */
	protected function getEntryStr(array $entry, $index, $entriesCount): string {
		$result = '';
		if ($index > 0) {
			$result = "\n";
		}

		$result .= $this->writeComments($entry);
		$result .= $this->writeReferences($entry);
		$result .= $this->writeFlags($entry);
		$result .= $this->writeContext($entry);
		$result .= $this->writeObsolete($entry);
		$result .= $this->writeMsgId($entry, 'msgid');
		$result .= $this->writeMsgId($entry, 'msgid_plural');
		$result .= $this->writeMsgStr($entry);

		if ($index === $entriesCount - 1) {
			$result = rtrim($result);
		}

		return $result;
	}

	/**
	 * @param array $entry
	 *
	 * @return string
	 */
	protected function writeComments(array $entry) {
		$result = '';

		if ($entry['tcomment'] !== '') {
			$result .= '# ' . $entry['tcomment'] . "\n";
		}

		if ($entry['ccomment'] !== '') {
			$result .= '#. ' . $entry['ccomment'] . "\n";
		}

		return $result;
	}

	/**
	 * @param array $entry
	 *
	 * @return string
	 */
	protected function writeFlags(array $entry) {
		$result = '';

		if (count($entry['flags']) > 0) {
			$result .= '#, ' . implode(', ', $entry['flags']) . "\n";
		}

		if (isset($entry['@'])) {
			$result .= '#@ ' . $entry['@'] . "\n";
		}

		return $result;
	}

	/**
	 * @param array $entry
	 *
	 * @return string
	 */
	protected function writeReferences(array $entry) {
		$result = '';

		if (count($entry['references']) > 0) {
			foreach ($entry['references'] as $ref) {
				$result .= '#: ' . $ref . "\n";
			}
		}

		return $result;
	}

	/**
	 * @param array $entry
	 *
	 * @return string
	 */
	protected function writeContext(array $entry) {
		$result = '';

		if ($entry['msgctxt'] !== '') {
			$result .= 'msgctxt ' . $this->cleanExport($entry['msgctxt']) . "\n";
		}

		return $result;
	}

	/**
	 * @param array $entry
	 *
	 * @return string
	 */
	protected function writeObsolete(array $entry) {
		return ($entry['obsolete']) ? '#~ ' : '';
	}

	/**
	 * @param array $entry
	 * @param string $type msgid or msgid_plural
	 *
	 * @return string
	 */
	protected function writeMsgId(array $entry, $type = 'msgid') {
		$result = '';

		if (!isset($entry[$type])) {
			return $result;
		}

		$result .= $type . ' ';
		if (is_array($entry[$type])) {
			foreach ($entry[$type] as $id) {
				$result .= $this->cleanExport($id) . "\n";
			}
		} else {
			$result .= $this->cleanExport($entry[$type]) . "\n";
		}

		return $result;
	}

	/**
	 * @param array $entry
	 *
	 * @return string
	 */
	protected function writeMsgStr(array $entry) {
		$result = '';

		if (!isset($entry['msgstr'])) {
			return $result;
		}

		$isPlural = isset($entry['msgid_plural']);

		foreach ($entry['msgstr'] as $i => $value) {
			if ($entry['obsolete']) {
				$result .= '#~ ';
			}

			if ($isPlural) {
				$result .= "msgstr[$i] ";
			} else {
				if ($i == 0) {
					$result .= 'msgstr ';
				}
			}

			$result .= $this->cleanExport($value) . "\n";
		}

		return $result;
	}

	/**
	 * @param string $string
	 *
	 * @return string
	 */
	protected function cleanExport(string $string) {
		$quote = '"';
		$slash = '\\';
		$newline = "\n";

		$replaces = [
			"$slash" => "$slash$slash",
			"$quote" => "$slash$quote",
			"\t" => '\t',
		];

		$string = str_replace(array_keys($replaces), array_values($replaces), $string);

		$po = $quote . implode("${slash}n$quote$newline$quote", explode($newline, $string)) . $quote;

		// remove empty strings
		return str_replace("$newline$quote$quote", '', $po);
	}

}
