<?php

namespace Translate\PotUpdater;

/**
 * Compares POT file contents to detect differences.
 */
class PotComparator {

	/**
	 * Compare existing strings with current strings
	 *
	 * @param array<string, array<string, mixed>> $existing Strings from existing POT file
	 * @param array<string, array<string, mixed>> $current Strings from current extraction
	 * @return array<string, array<string, mixed>>
	 */
	public function compare(array $existing, array $current): array {
		$added = array_diff_key($current, $existing);
		$removed = array_diff_key($existing, $current);
		$common = array_intersect_key($existing, $current);

		$changed = [];
		$unchanged = [];

		foreach ($common as $key => $existingData) {
			$currentData = $current[$key];

			if ($this->hasChanges($existingData, $currentData)) {
				$changed[$key] = [
					'existing' => $existingData,
					'current' => $currentData,
				];
			} else {
				$unchanged[$key] = $existingData;
			}
		}

		return [
			'added' => $added,
			'removed' => $removed,
			'changed' => $changed,
			'unchanged' => $unchanged,
		];
	}

	/**
	 * Check if a string entry has changes
	 *
	 * @param array<string, mixed> $existing Existing entry
	 * @param array<string, mixed> $current Current entry
	 * @param bool $ignoreReferences Whether to ignore reference changes
	 * @return bool
	 */
	public function hasChanges(array $existing, array $current, bool $ignoreReferences = false): bool {
		// Check msgid_plural change
		if ($existing['msgid_plural'] !== $current['msgid_plural']) {
			return true;
		}

		// Check context change
		if ($existing['msgctxt'] !== $current['msgctxt']) {
			return true;
		}

		// Check references unless ignored
		if (!$ignoreReferences) {
			$existingRefs = $existing['references'];
			$currentRefs = $current['references'];
			sort($existingRefs);
			sort($currentRefs);

			if ($existingRefs !== $currentRefs) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if there are any meaningful differences
	 *
	 * @param array<string, array<string, mixed>> $diff Comparison result
	 * @param bool $ignoreReferences Whether to ignore reference-only changes
	 * @return bool
	 */
	public function hasDifferences(array $diff, bool $ignoreReferences = false): bool {
		if (!empty($diff['added']) || !empty($diff['removed'])) {
			return true;
		}

		if (!$ignoreReferences && !empty($diff['changed'])) {
			return true;
		}

		return false;
	}

	/**
	 * Get summary statistics
	 *
	 * @param array<string, array<string, mixed>> $diff Comparison result
	 * @return array<string, int>
	 */
	public function getSummary(array $diff): array {
		return [
			'added' => count($diff['added']),
			'removed' => count($diff['removed']),
			'changed' => count($diff['changed']),
			'unchanged' => count($diff['unchanged']),
			'total_existing' => count($diff['removed']) + count($diff['changed']) + count($diff['unchanged']),
			'total_current' => count($diff['added']) + count($diff['changed']) + count($diff['unchanged']),
		];
	}

}
