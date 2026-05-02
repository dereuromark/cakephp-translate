<?php

namespace Translate\Filesystem;

class Creator {

	/**
	 * Locale codes are validated against this regex before being used as filesystem segments
	 * (Issue #6: path traversal via locale codes).
	 *
	 * @var string
	 */
	protected const LOCALE_REGEX = '/^[a-z]{2,3}(_[A-Z]{2})?$/';

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

		$basePathReal = (string)realpath($path);
		if ($basePathReal === '') {
			return ['Base path could not be resolved: ' . $path];
		}

		$failures = [];
		foreach ($languages as $language) {
			$language = (string)$language;
			if (!preg_match(static::LOCALE_REGEX, $language)) {
				$failures[] = 'Invalid locale code rejected: ' . $language;

				continue;
			}

			$target = $path . $language . DS;
			$normalizedParent = $this->normalizeContainment(rtrim($target, '/' . DS));
			if (!str_starts_with($normalizedParent, $basePathReal)) {
				$failures[] = 'Locale directory escapes base path: ' . $language;

				continue;
			}

			if (!$handle->create($target)) {
				$failures = array_merge($failures, $handle->errors());
			}
		}

		if ($failures) {
			return $failures;
		}

		return true;
	}

	/**
	 * Collapse `.`/`..` without touching the filesystem (the directory may not exist yet).
	 *
	 * @param string $path Absolute path
	 * @return string
	 */
	protected function normalizeContainment(string $path): string {
		$path = str_replace(['\\', '//'], ['/', '/'], $path);
		$isAbsolute = str_starts_with($path, '/');
		$segments = [];
		foreach (explode('/', $path) as $segment) {
			if ($segment === '' || $segment === '.') {
				continue;
			}
			if ($segment === '..') {
				array_pop($segments);

				continue;
			}
			$segments[] = $segment;
		}

		return ($isAbsolute ? '/' : '') . implode('/', $segments);
	}

}
