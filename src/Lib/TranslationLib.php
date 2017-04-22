<?php
namespace Translate\Lib;

use Cake\Filesystem\Folder;
use Cake\I18n\Parser\PoFileParser;
use Cake\Utility\Inflector;
use Sepia\FileHandler;
use Sepia\PoParser;

class TranslationLib {

	/**
	 * Singular + plural!
	 *
	 * @return array
	 */
	public function getResourceNames() {
		$names = [];

		$list = [];

		foreach ($list as $name) {
			$name = Inflector::humanize(Inflector::underscore($name));

			$names[] = $name;
			$singular = Inflector::singularize($name);
			if (!in_array($singular, $names)) {
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
				$potFiles[pathinfo($file, PATHINFO_FILENAME)] = pathinfo($file, PATHINFO_FILENAME);
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
			$fileHandler = new FileHandler($file);

			$poParser = new PoParser($fileHandler);
			$entries = $poParser->parse();

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
			$fileHandler = new FileHandler($file);

			$poParser = new PoParser($fileHandler);
			$entries = $poParser->parse();

			$content = $this->_map($entries);
		}

		return $content;
	}

	public function exportPoFile($data) {
	}

	/**
	 * @param array $entries
	 *
	 * @return array
	 */
	protected function _map(array $entries) {
		$translations = [];

		foreach ($entries as $entry) {
			$record = [
				'name' => implode('', $entry['msgid']),
				'content' => $this->_content($entry),
				'comment' => $this->_comment($entry),
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
				$record['occurances'] = implode("\n", $entry['reference']);
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
	 * @return null|string
	 */
	protected function _comment(array $entry) {
		$rows = [];

		$keys = ['ccomment', 'tcomment', 'flags'];
		foreach ($entry as $key => $value) {
			if (!in_array($key, $keys)) {
				continue;
			}
			switch ($key) {
				case 'ccomment':
					foreach ($value as $v) {
						$rows[] = '#. ' . $v;
					}
					break;
				case 'tcomment':
					foreach ($value as $v) {
						$rows[] = '#  ' . $v;
					}
					break;
				case 'flags':
					$rows[] = '#, ' . implode(',', $value);
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
	 * @return null|string
	 */
	protected function _content(array $entry) {
		if (isset($entry['msgstr'])) {
			return implode('', $entry['msgstr']);
		}

		if (isset($entry['msgstr[0]'])) {
			return implode('', $entry['msgstr[0]']);
		}

		return null;
	}

}
