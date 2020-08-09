<?php

namespace Translate\Lib;

use Cake\Filesystem\Folder;
use Cake\I18n\Parser\PoFileParser;
use Cake\Utility\Inflector;
use PoParser\Parser;

class TranslationLib {

	/**
	 * Singular + plural!
	 *
	 * @param array $list
	 *
	 * @return array
	 */
	public function getResourceNames(array $list = []) {
		$names = [];

		foreach ($list as $name) {
			$name = Inflector::humanize(Inflector::underscore($name));

			$names[] = $name;
			$singular = Inflector::singularize($name);
			if (!in_array($singular, $names, true)) {
				$names[] = $singular;
			}
		}

		return $names;
	}

	/**
	 * @return array
	 */
	public function getPotFiles() {
		$folder = new Folder(LOCALE);
		$files = $folder->read(true, true);
		$potFiles = [];
		if (!empty($files[1])) {
			foreach ($files[1] as $file) {
				if (pathinfo($file, PATHINFO_EXTENSION) !== 'pot') {
					continue;
				}

				$filename = pathinfo($file, PATHINFO_FILENAME);
				$potFiles[$filename] = $filename;
			}
		}

		return $potFiles;
	}

	/**
	 * @return array
	 */
	public function getPoFileLanguages() {
		$folder = new Folder(LOCALE);
		$files = $folder->read(true, true);

		$languages = [];
		if (!empty($files[0])) {
			$languages = $files[0];
		}

		return $languages;
	}

	/**
	 * @param string $lang
	 *
	 * @return array
	 */
	public function getPoFiles($lang) {
		$folder = new Folder(LOCALE . $lang . DS);
		$files = $folder->read(true, true);

		$poFiles = [];
		if (!empty($files[1])) {
			foreach ($files[1] as $file) {
				if (pathinfo($file, PATHINFO_EXTENSION) !== 'po') {
					continue;
				}
				$poFiles[$lang . '_' . pathinfo($file, PATHINFO_FILENAME)] = pathinfo($file, PATHINFO_FILENAME);
			}
		}

		return $poFiles;
	}

	/**
	 * @param string $domain
	 * @param string $dir
	 *
	 * @return array
	 */
	public function extractPotFile($domain, $dir = LOCALE) {
		$names = [];

		$file = $dir . $domain . '.pot';
		$content = [];

		if (file_exists($file)) {
			$poParser = new Parser();
			$poParser->read($file);
			$entries = $poParser->getEntriesAsArrays();

			$content = $this->_map($entries);
		}

		return $content;
	}

	/**
	 * @param string $domain
	 * @param string $lang
	 * @param string $dir
	 *
	 * @return array
	 */
	public function extractPoFile($domain, $lang, $dir = LOCALE) {
		$names = [];

		$file = $dir . $lang . DS . $domain . '.po';
		$content = [];

		if (file_exists($file)) {
			$poParser = new Parser();
			$poParser->read($file);
			$entries = $poParser->getEntriesAsArrays();

			$content = $this->_map($entries);
		}

		return $content;
	}

	/**
	 * @param array $data
	 * @return void
	 */
	public function exportPoFile($data) {
		//TODO
	}

	/**
	 * @param array $entries
	 * @return array
	 */
	protected function _map(array $entries) {
		$translations = [];

		foreach ($entries as $entry) {
			if (empty($entry['msgid'])) {
				continue;
			}

			$record = [
				'name' => is_array($entry['msgid']) ? implode('', $entry['msgid']) : $entry['msgid'],
				'content' => $this->_content($entry),
				'comments' => $this->_comment($entry),
			];

			if (!empty($entry['msgid_plural'])) {
				$record['plural'] = $entry['msgid_plural'];
				for ($i = 1; $i <= 6; $i++) {
					if (!isset($entry['msgstr'][$i])) {
						break;
					}

					$record['plural_' . ($i + 1)] = $entry['msgstr'][$i];
				}
			}
			if (!empty($entry['msgctxt'])) {
				$record['context'] = $entry['msgctxt'];
			}

			if (!empty($entry['references'])) {
				$record['references'] = is_array($entry['references']) ? implode("\n", $entry['references']) : $entry['references'];
			}
			if (!empty($entry['flags'])) {
				$record['flags'] = $entry['flags'];
			}

			$translations[] = $record;
		}

		return $translations;
	}

	/**
	 * @deprecated Too buggy
	 * @param array $entries
	 *
	 * @return array
	 */
	protected function _mapSepia(array $entries) {
		$translations = [];

		foreach ($entries as $entry) {
			$record = [
				'name' => implode('', $entry['msgid']),
				'content' => $this->_content($entry),
				'comments' => $this->_comment($entry),
			];
			if (!empty($entry['msgid_plural'])) {
				$record['plural'] = implode('', $entry['msgid_plural']);
				for ($i = 1; $i <= 6; $i++) {
					if (!isset($entry['msgstr[' . $i . ']'])) {
						break;
					}

					$record['plural_' . ($i + 1)] = implode('', $entry['msgstr[' . $i . ']']);
				}
			}
			if (!empty($entry['msgctxt'])) {
				$record['context'] = implode('', $entry['msgctxt']);
			}
			if (!empty($entry['reference'])) {
				$record['references'] = implode("\n", $entry['reference']);
			}
			if (!empty($entry['flags'])) {
				$record['flags'] = $entry['flags'];
			}

			$translations[] = $record;
		}

		return $translations;
	}

	/**
	 * @param string $file
	 *
	 * @return array
	 */
	public function parseFile($file) {
		if (!file_exists($file)) {
			return [];
		}

		$poParser = new PoFileParser();
		$content = $poParser->parse($file);

		return $content;
	}

	/**
	 * @param array $entry
	 *
	 * @return string|null
	 */
	protected function _comment(array $entry) {
		$rows = [];

		$keys = ['ccomment', 'tcomment'];
		foreach ($entry as $key => $value) {
			if (!in_array($key, $keys) || $entry[$key] === '') {
				continue;
			}
			switch ($key) {
				case 'ccomment':
					$rows[] = '#. ' . $value;

					break;
				case 'tcomment':
					$rows[] = '#  ' . $value;

					break;
			}
		}

		if (!$rows) {
			return null;
		}

		return implode("\n", $rows);
	}

	/**
	 * @param array $entry
	 *
	 * @return string|null
	 */
	protected function _content(array $entry) {
		if (isset($entry['msgstr'])) {
			return is_array($entry['msgstr']) ? $entry['msgstr'][0] : $entry['msgstr'];
		}

		return null;
	}

}
