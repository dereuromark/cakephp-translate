<?php

namespace Translate\Utility;

use Cake\Http\Exception\NotFoundException;

/**
 * Utility class for resolving code reference paths from PO files.
 */
class ReferenceResolver {

	/**
	 * Parse a references string into an array of reference entries.
	 *
	 * @param string|null $references Newline-separated references
	 * @return array<string>
	 */
	public static function parseReferences(?string $references): array {
		if ($references === null || $references === '') {
			return [];
		}

		$result = [];
		foreach (explode(PHP_EOL, $references) as $line) {
			$line = trim($line);
			if ($line !== '') {
				$result[] = $line;
			}
		}

		return $result;
	}

	/**
	 * Parse a single reference string into path and line numbers.
	 *
	 * @param string $reference Reference string like "path/to/file.php:10;20"
	 * @return array{path: string, lines: array<string>}
	 */
	public static function parseReference(string $reference): array {
		$parts = explode(':', $reference, 2);
		$path = $parts[0];
		$lines = isset($parts[1]) ? explode(';', $parts[1]) : [];

		return [
			'path' => $path,
			'lines' => $lines,
		];
	}

	/**
	 * Resolve a reference path to an absolute file path.
	 *
	 * Handles:
	 * - Leading ./ removal
	 * - Relative path resolution
	 * - Project path prefixing
	 * - Fallback to ROOT for app-relative paths
	 *
     * @param string $referencePath The path from the reference
     * @param string|null $projectPath The project's base path (null for ROOT)
     * @throws \Cake\Http\Exception\NotFoundException If file cannot be found
     * @return string The resolved absolute file path
	 */
	public static function resolveFilePath(string $referencePath, ?string $projectPath): string {
		// Clean up reference path - remove leading ./
		$referencePath = preg_replace('#^\./#', '', $referencePath);

		// Resolve project base path
		$basePath = static::resolveProjectPath($projectPath);

		// Try to find the file
		$file = $basePath . $referencePath;
		$resolvedFile = realpath($file);

		if ($resolvedFile === false) {
			// Try from ROOT if project path didn't work (for app-relative paths like ./vendor/...)
			$file = ROOT . DS . $referencePath;
			$resolvedFile = realpath($file);
		}

		if ($resolvedFile === false || !file_exists($resolvedFile)) {
			throw new NotFoundException('File not found: ' . $basePath . $referencePath);
		}

		return $resolvedFile;
	}

	/**
	 * Resolve a project path to an absolute directory path.
	 *
     * @param string|null $projectPath The project's configured path
     * @throws \Cake\Http\Exception\NotFoundException If path is invalid
     * @return string Absolute path with trailing slash
	 */
	public static function resolveProjectPath(?string $projectPath): string {
		if (!$projectPath) {
			$path = ROOT;
		} elseif (!str_starts_with($projectPath, '/')) {
			$path = ROOT . DS . $projectPath;
		} else {
			$path = $projectPath;
		}

		$resolved = realpath($path);
		if ($resolved === false || !is_dir($resolved)) {
			throw new NotFoundException('Path not found: ' . ($projectPath ?? 'ROOT'));
		}

		return rtrim($resolved, '/') . '/';
	}

	/**
	 * Get a specific reference from a references string by index.
	 *
     * @param string|null $references Newline-separated references
     * @param int $index 0-based index
     * @throws \Cake\Http\Exception\NotFoundException If index not found
     * @return string The reference string
	 */
	public static function getReferenceByIndex(?string $references, int $index): string {
		$parsed = static::parseReferences($references);

		if (!isset($parsed[$index])) {
			throw new NotFoundException('Could not find reference `' . $index . '`');
		}

		return $parsed[$index];
	}

}
