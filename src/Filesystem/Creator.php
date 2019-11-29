<?php

namespace Translate\Filesystem;

use Cake\Filesystem\Folder;

class Creator {

	/**
	 * @param string $path
	 *
	 * @return array folders
	 */
	public function findLocaleFolders($path) {
		$handle = new Folder($path);
		$folders = $handle->read(true, true);

		return $folders[0];
	}

	/**
	 * @param array $languages
	 * @param string $path
	 *
	 * @return array|bool Bool success or Array of failures
	 */
	public function createLocaleFolders(array $languages, $path) {
		$handle = new Folder($path, true);
		if ($handle->errors()) {
			return $handle->errors();
		}
		foreach ($languages as $language) {
			if (!$handle->create($path . $language . DS)) {
				return $handle->errors();
			}
		}
		return true;
	}

}
