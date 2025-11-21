<?php

namespace Translate\Service;

use Translate\Parser\PoParser;

/**
 * Service for analyzing PO/POT files and detecting common issues.
 */
class PoAnalyzerService {

	/**
	 * @var array<string, array<string, mixed>>
	 */
	protected array $issues = [];

	/**
	 * @var array<string, int>
	 */
	protected array $stats = [
		'total' => 0,
		'translated' => 0,
		'untranslated' => 0,
		'fuzzy' => 0,
		'plurals' => 0,
		'with_context' => 0,
	];

	/**
	 * Analyze PO file content and return issues.
	 *
	 * @param string $content PO file content
	 * @return array{issues: array<string, array<string, mixed>>, stats: array<string, int>, suggestions: array<string>}
	 */
	public function analyze(string $content): array {
		$this->issues = [];
		$this->stats = [
			'total' => 0,
			'translated' => 0,
			'untranslated' => 0,
			'fuzzy' => 0,
			'plurals' => 0,
			'with_context' => 0,
		];

		// Write to temp file for PoParser
		$tempFile = TMP . 'po_analyze_' . uniqid() . '.po';
		file_put_contents($tempFile, $content);

		try {
			$poParser = new PoParser();
			$poParser->read($tempFile);
			$entries = $poParser->getEntriesAsArrays();

			foreach ($entries as $entry) {
				$this->analyzeEntry($entry);
			}
		} finally {
			@unlink($tempFile);
		}

		return [
			'issues' => $this->issues,
			'stats' => $this->stats,
			'suggestions' => $this->generateSuggestions(),
		];
	}

	/**
	 * Analyze a single PO entry.
	 *
	 * @param array<string, mixed> $entry
	 * @return void
	 */
	protected function analyzeEntry(array $entry): void {
		// Skip header entry
		if (empty($entry['msgid'])) {
			return;
		}

		$msgid = is_array($entry['msgid']) ? implode('', $entry['msgid']) : $entry['msgid'];
		$msgstr = $this->getMsgstr($entry);

		$this->stats['total']++;

		// Check for plural
		if (!empty($entry['msgid_plural'])) {
			$this->stats['plurals']++;
		}

		// Check for context
		if (!empty($entry['msgctxt'])) {
			$this->stats['with_context']++;
		}

		// Check if translated
		if (empty($msgstr)) {
			$this->stats['untranslated']++;
		} else {
			$this->stats['translated']++;
		}

		// Check for fuzzy
		if (!empty($entry['flags']) && in_array('fuzzy', $entry['flags'], true)) {
			$this->stats['fuzzy']++;
		}

		// Run issue checks
		$this->checkPlaceholderMismatch($msgid, $entry);
		$this->checkPluralPlaceholderMismatch($entry);
		$this->checkEmptyTranslation($msgid, $entry);
		$this->checkWhitespaceMismatch($msgid, $msgstr, $entry);
		$this->checkHtmlMismatch($msgid, $msgstr);
	}

	/**
	 * Get msgstr value handling plurals.
	 *
	 * @param array<string, mixed> $entry
	 * @return string
	 */
	protected function getMsgstr(array $entry): string {
		if (is_array($entry['msgstr'])) {
			// For plurals, check first form
			return trim($entry['msgstr'][0] ?? '');
		}

		return trim($entry['msgstr'] ?? '');
	}

	/**
	 * Check for placeholder mismatch between msgid and msgstr.
	 *
	 * @param string $msgid
	 * @param array<string, mixed> $entry
	 * @return void
	 */
	protected function checkPlaceholderMismatch(string $msgid, array $entry): void {
		$msgstr = $this->getMsgstr($entry);
		if (empty($msgstr)) {
			return;
		}

		// Check {0}, {1} style
		preg_match_all('/\{\d+\}/', $msgid, $expectedBrace);
		preg_match_all('/\{\d+\}/', $msgstr, $actualBrace);

		if ($this->placeholdersDiffer($expectedBrace[0], $actualBrace[0])) {
			$this->addIssue($msgid, 'placeholder_mismatch', [
				'type' => 'brace',
				'expected' => $expectedBrace[0],
				'actual' => $actualBrace[0],
				'message' => sprintf(
					'Brace placeholder mismatch: expected %s, got %s',
					json_encode($expectedBrace[0]),
					json_encode($actualBrace[0]),
				),
			]);
		}

		// Check %s, %d style
		preg_match_all('/%(?:\d+\$)?[sdfboxXeEgGcup]/', $msgid, $expectedSprintf);
		preg_match_all('/%(?:\d+\$)?[sdfboxXeEgGcup]/', $msgstr, $actualSprintf);

		if ($this->placeholdersDiffer($expectedSprintf[0], $actualSprintf[0])) {
			$this->addIssue($msgid, 'placeholder_mismatch', [
				'type' => 'sprintf',
				'expected' => $expectedSprintf[0],
				'actual' => $actualSprintf[0],
				'message' => sprintf(
					'Sprintf placeholder mismatch: expected %s, got %s',
					json_encode($expectedSprintf[0]),
					json_encode($actualSprintf[0]),
				),
			]);
		}
	}

	/**
	 * Check for placeholder mismatch between msgid and msgid_plural.
	 *
	 * @param array<string, mixed> $entry
	 * @return void
	 */
	protected function checkPluralPlaceholderMismatch(array $entry): void {
		if (empty($entry['msgid_plural'])) {
			return;
		}

		$msgid = is_array($entry['msgid']) ? implode('', $entry['msgid']) : $entry['msgid'];
		$msgidPlural = is_array($entry['msgid_plural']) ? implode('', $entry['msgid_plural']) : $entry['msgid_plural'];

		// Check {0} style placeholders
		preg_match_all('/\{\d+\}/', $msgid, $singularBrace);
		preg_match_all('/\{\d+\}/', $msgidPlural, $pluralBrace);

		if ($this->placeholdersDiffer($singularBrace[0], $pluralBrace[0])) {
			$this->addIssue($msgid, 'plural_placeholder_mismatch', [
				'singular' => $singularBrace[0],
				'plural' => $pluralBrace[0],
				'msgid_plural' => $msgidPlural,
				'message' => sprintf(
					'Plural form uses different placeholders: singular has %s, plural has %s',
					json_encode($singularBrace[0]),
					json_encode($pluralBrace[0]),
				),
			]);
		}

		// Check %d/%s style - different style in plural is common issue
		preg_match_all('/%(?:\d+\$)?[sdfboxXeEgGcup]/', $msgid, $singularSprintf);
		preg_match_all('/%(?:\d+\$)?[sdfboxXeEgGcup]/', $msgidPlural, $pluralSprintf);

		// Check if singular uses {0} but plural uses %d (mixed styles)
		if (!empty($singularBrace[0]) && empty($pluralBrace[0]) && !empty($pluralSprintf[0])) {
			$this->addIssue($msgid, 'mixed_placeholder_styles', [
				'singular_style' => 'brace {0}',
				'plural_style' => 'sprintf %d',
				'msgid_plural' => $msgidPlural,
				'message' => 'Mixed placeholder styles: singular uses {0}, plural uses %d - should be consistent',
			]);
		}
	}

	/**
	 * Check for empty translations that might need attention.
	 *
	 * @param string $msgid
	 * @param array<string, mixed> $entry
	 * @return void
	 */
	protected function checkEmptyTranslation(string $msgid, array $entry): void {
		$msgstr = $this->getMsgstr($entry);
		if (!empty($msgstr)) {
			return;
		}

		// Only flag as issue if it's not fuzzy (fuzzy entries are work-in-progress)
		$isFuzzy = !empty($entry['flags']) && in_array('fuzzy', $entry['flags'], true);
		if (!$isFuzzy) {
			$this->addIssue($msgid, 'untranslated', [
				'message' => 'String is not translated',
			]);
		}
	}

	/**
	 * Check for whitespace mismatches.
	 *
	 * @param string $msgid
	 * @param string $msgstr
	 * @param array<string, mixed> $entry
	 * @return void
	 */
	protected function checkWhitespaceMismatch(string $msgid, string $msgstr, array $entry): void {
		if (empty($msgstr)) {
			return;
		}

		// Check leading/trailing whitespace
		$msgidLeading = strlen($msgid) - strlen(ltrim($msgid));
		$msgstrLeading = strlen($msgstr) - strlen(ltrim($msgstr));
		$msgidTrailing = strlen($msgid) - strlen(rtrim($msgid));
		$msgstrTrailing = strlen($msgstr) - strlen(rtrim($msgstr));

		if ($msgidLeading !== $msgstrLeading || $msgidTrailing !== $msgstrTrailing) {
			$this->addIssue($msgid, 'whitespace_mismatch', [
				'message' => sprintf(
					'Whitespace differs: original has %d leading/%d trailing, translation has %d leading/%d trailing',
					$msgidLeading,
					$msgidTrailing,
					$msgstrLeading,
					$msgstrTrailing,
				),
			]);
		}
	}

	/**
	 * Check for HTML tag mismatches.
	 *
	 * @param string $msgid
	 * @param string $msgstr
	 * @return void
	 */
	protected function checkHtmlMismatch(string $msgid, string $msgstr): void {
		if (empty($msgstr)) {
			return;
		}

		preg_match_all('/<[^>]+>/', $msgid, $expectedTags);
		preg_match_all('/<[^>]+>/', $msgstr, $actualTags);

		if ($expectedTags[0] !== $actualTags[0]) {
			$this->addIssue($msgid, 'html_mismatch', [
				'expected' => $expectedTags[0],
				'actual' => $actualTags[0],
				'message' => 'HTML tags differ between original and translation',
			]);
		}
	}

	/**
	 * Check if two placeholder arrays differ.
	 *
	 * @param array<string> $expected
	 * @param array<string> $actual
	 * @return bool
	 */
	protected function placeholdersDiffer(array $expected, array $actual): bool {
		if (count($expected) !== count($actual)) {
			return true;
		}

		sort($expected);
		sort($actual);

		return $expected !== $actual;
	}

	/**
	 * Add an issue to the issues list.
	 *
	 * @param string $msgid
	 * @param string $type
	 * @param array<string, mixed> $details
	 * @return void
	 */
	protected function addIssue(string $msgid, string $type, array $details): void {
		if (!isset($this->issues[$msgid])) {
			$this->issues[$msgid] = [];
		}
		$this->issues[$msgid][$type] = $details;
	}

	/**
	 * Generate improvement suggestions based on analysis.
	 *
	 * @return array<string>
	 */
	protected function generateSuggestions(): array {
		$suggestions = [];

		if ($this->stats['untranslated'] > 0) {
			$percentage = round(($this->stats['untranslated'] / $this->stats['total']) * 100);
			$suggestions[] = sprintf(
				'%d strings (%d%%) are untranslated',
				$this->stats['untranslated'],
				$percentage,
			);
		}

		if ($this->stats['fuzzy'] > 0) {
			$suggestions[] = sprintf(
				'%d strings are marked as fuzzy and need review',
				$this->stats['fuzzy'],
			);
		}

		$placeholderIssues = 0;
		$mixedStyleIssues = 0;
		foreach ($this->issues as $issues) {
			if (isset($issues['placeholder_mismatch']) || isset($issues['plural_placeholder_mismatch'])) {
				$placeholderIssues++;
			}
			if (isset($issues['mixed_placeholder_styles'])) {
				$mixedStyleIssues++;
			}
		}

		if ($placeholderIssues > 0) {
			$suggestions[] = sprintf(
				'%d strings have placeholder mismatches that will cause runtime errors',
				$placeholderIssues,
			);
		}

		if ($mixedStyleIssues > 0) {
			$suggestions[] = sprintf(
				'%d strings mix {0} and %%d placeholder styles - should use consistent style',
				$mixedStyleIssues,
			);
		}

		return $suggestions;
	}

}
