<?php

namespace Translate\Service;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Scans PHP source for __d() and __dn() calls and reports which translation
 * domains are in use, where, and which msgids are referenced for each.
 *
 * The scanner does not depend on a CakePHP application bootstrap or the
 * translation cache — it works on raw PHP source via token_get_all().
 */
class DomainScannerService {

	/**
	 * Functions whose first argument is the translation domain.
	 *
	 * The value is the position (0-indexed) of the msgid argument relative
	 * to the function call, after the domain.
	 *
	 * @var array<string, int>
	 */
	protected const DOMAIN_FUNCTIONS = [
		'__d' => 1,
		'__dn' => 1,
		'__dx' => 2,
		'__dxn' => 2,
	];

	/**
	 * Default sub-directories to scan inside each project root.
	 *
	 * @var array<string>
	 */
	public const DEFAULT_SUBDIRS = ['src', 'templates', 'config'];

	/**
	 * Scan a list of root paths for translation calls and return per-domain usage.
	 *
	 * @param array<string> $paths Absolute paths. Subdirs (src/, templates/, config/) are scanned.
	 * @param array<string> $subdirs Sub-directories under each path to scan. Defaults to src/templates/config.
	 * @return array<string, array{
	 *     msgids: array<string, array<string, array<int>>>,
	 *     fileCount: int,
	 *     callCount: int,
	 * }> Map domain => {msgids => msgid => file => [lines], fileCount, callCount}
	 */
	public function scan(array $paths, array $subdirs = self::DEFAULT_SUBDIRS): array {
		$result = [];
		foreach ($paths as $root) {
			$root = rtrim($root, DIRECTORY_SEPARATOR);
			foreach ($subdirs as $sub) {
				$dir = $root . DIRECTORY_SEPARATOR . $sub;
				if (!is_dir($dir)) {
					continue;
				}
				$this->scanDirectory($dir, $result);
			}
		}

		// Compute per-domain summary stats
		foreach ($result as &$entry) {
			$files = [];
			$calls = 0;
			foreach ($entry['msgids'] as $fileLines) {
				foreach ($fileLines as $file => $lines) {
					$files[$file] = true;
					$calls += count($lines);
				}
			}
			$entry['fileCount'] = count($files);
			$entry['callCount'] = $calls;
			ksort($entry['msgids']);
		}
		unset($entry);
		ksort($result);

		return $result;
	}

	/**
	 * Discover the project root + its plugins/ folder as scan paths.
	 *
	 * @param string $appRoot Absolute path to the project root (typically ROOT).
	 * @return array<string> Sorted list of directories to feed into scan().
	 */
	public function discoverPaths(string $appRoot): array {
		$paths = [$appRoot];
		$pluginsDir = rtrim($appRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'plugins';
		if (is_dir($pluginsDir)) {
			$entries = scandir($pluginsDir) ?: [];
			foreach ($entries as $name) {
				if ($name === '.' || $name === '..') {
					continue;
				}
				$pluginPath = $pluginsDir . DIRECTORY_SEPARATOR . $name;
				if (is_dir($pluginPath)) {
					$paths[] = $pluginPath;
				}
			}
		}

		return $paths;
	}

	/**
	 * @param string $dir
	 * @param array<string, array{msgids: array<string, array<string, array<int>>>}> $result
	 * @return void
	 */
	protected function scanDirectory(string $dir, array &$result): void {
		$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS));
		foreach ($it as $file) {
			$path = (string)$file;
			if (!str_ends_with($path, '.php')) {
				continue;
			}
			// Skip nested vendor/ and node_modules/ inside plugins
			if (str_contains($path, DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR)
				|| str_contains($path, DIRECTORY_SEPARATOR . 'node_modules' . DIRECTORY_SEPARATOR)
			) {
				continue;
			}
			$this->scanFile($path, $result);
		}
	}

	/**
	 * @param string $path
	 * @param array<string, array{msgids: array<string, array<string, array<int>>>}> $result
	 * @return void
	 */
	protected function scanFile(string $path, array &$result): void {
		$src = (string)file_get_contents($path);
		// Cheap pre-filter: skip files with no __d at all
		if (!str_contains($src, '__d')) {
			return;
		}

		$tokens = token_get_all($src);
		// Strip whitespace/inline-html for easier walking
		$compact = [];
		foreach ($tokens as $t) {
			if (is_array($t) && ($t[0] === T_WHITESPACE || $t[0] === T_INLINE_HTML)) {
				continue;
			}
			$compact[] = $t;
		}
		$count = count($compact);

		for ($i = 0; $i < $count - 1; $i++) {
			$tok = $compact[$i];
			if (!is_array($tok) || $tok[0] !== T_STRING) {
				continue;
			}
			$name = $tok[1];
			if (!isset(static::DOMAIN_FUNCTIONS[$name])) {
				continue;
			}
			// Skip method/static calls like $foo->__d() or Foo::__d()
			$prev = $i > 0 ? $compact[$i - 1] : null;
			if (is_array($prev) && (in_array($prev[0], [T_OBJECT_OPERATOR, T_DOUBLE_COLON, T_NULLSAFE_OBJECT_OPERATOR], true))) {
				continue;
			}
			if ($compact[$i + 1] !== '(') {
				continue;
			}
			$line = (int)$tok[2];
			// Read first string argument (the domain)
			$domain = $this->readStringArg($compact, $i + 2);
			if ($domain === null) {
				continue;
			}
			// Read msgid: it's the next string after a comma
			$msgidPos = $this->advancePastArg($compact, $i + 2);
			if ($msgidPos === null || ($compact[$msgidPos] ?? null) !== ',') {
				$result[$domain]['msgids'][''][$path][] = $line;

				continue;
			}
			$msgid = $this->readStringArg($compact, $msgidPos + 1);
			$key = $msgid ?? '';
			$result[$domain]['msgids'][$key][$path][] = $line;
		}
	}

	/**
	 * If $tokens[$pos] is a single-string literal, return its decoded value.
	 *
	 * @param array<array{0: int, 1: string, 2: int}|string> $tokens
	 * @param int $pos
	 * @return string|null
	 */
	protected function readStringArg(array $tokens, int $pos): ?string {
		$tok = $tokens[$pos] ?? null;
		if (!is_array($tok)) {
			return null;
		}
		$type = $tok[0];
		$value = (string)$tok[1];
		if ($type === T_CONSTANT_ENCAPSED_STRING) {
			$first = $value[0] ?? '';
			$inner = substr($value, 1, -1);
			if ($first === '"') {
				return stripcslashes($inner);
			}

			// Single-quoted: only \' and \\ are processed
			return strtr($inner, ['\\\'' => "'", '\\\\' => '\\']);
		}

		return null;
	}

	/**
	 * Advance the cursor past one balanced argument (literal, method call,
	 * concatenation, etc.) and return the position of the comma or `)` that
	 * follows it. Returns null if not balanced.
	 *
	 * @param array<array{0: int, 1: string, 2: int}|string> $tokens
	 * @param int $start
	 * @return int|null
	 */
	protected function advancePastArg(array $tokens, int $start): ?int {
		$depth = 0;
		$count = count($tokens);
		for ($i = $start; $i < $count; $i++) {
			$tok = $tokens[$i];
			if (in_array($tok, ['(', '[', '{'], true)) {
				$depth++;

				continue;
			}
			if (in_array($tok, [')', ']', '}'], true)) {
				if ($depth === 0) {
					return $i;
				}
				$depth--;

				continue;
			}
			if ($depth === 0 && $tok === ',') {
				return $i;
			}
		}

		return null;
	}

}
