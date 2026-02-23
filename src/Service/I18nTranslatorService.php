<?php
declare(strict_types=1);

namespace Translate\Service;

use Cake\Log\Log;
use Cake\ORM\Locator\LocatorAwareTrait;
use Exception;
use Translate\Translator\Translator;

/**
 * I18n Translator Service
 *
 * Provides translation services for TranslateBehavior i18n entries.
 * Uses the configured translation engines (Google, etc.) with caching.
 */
class I18nTranslatorService {

	use LocatorAwareTrait;

	/**
	 * @var \Translate\Translator\Translator|null
	 */
	protected ?Translator $translator = null;

	/**
	 * Translate text using configured translation engine
	 *
	 * @param string $text Source text
	 * @param string $targetLocale Target locale (e.g., 'de', 'de_DE')
	 * @param string $sourceLocale Source locale (e.g., 'en', 'en_US')
	 * @return string|null Translated text or null on failure
	 */
	public function translate(string $text, string $targetLocale, string $sourceLocale = 'en'): ?string {
		if (empty($text)) {
			return null;
		}

		// Extract language code from locale (de_DE -> de)
		$targetLang = $this->extractLanguageCode($targetLocale);
		$sourceLang = $this->extractLanguageCode($sourceLocale);

		if ($targetLang === $sourceLang) {
			return $text; // Same language, no translation needed
		}

		try {
			$translator = $this->getTranslator();
			$result = $translator->translate($text, $targetLang, $sourceLang);

			return $result;
		} catch (Exception $e) {
			Log::warning('I18nTranslatorService: Translation failed - ' . $e->getMessage());

			return null;
		}
	}

	/**
	 * Translate multiple texts in batch
	 *
	 * @param array<string> $texts Array of texts to translate
	 * @param string $targetLocale Target locale
	 * @param string $sourceLocale Source locale
	 * @return array<string|null> Array of translated texts
	 */
	public function translateBatch(array $texts, string $targetLocale, string $sourceLocale = 'en'): array {
		$results = [];

		foreach ($texts as $key => $text) {
			$results[$key] = $this->translate($text, $targetLocale, $sourceLocale);
		}

		return $results;
	}

	/**
	 * Get translation suggestions from multiple engines
	 *
	 * @param string $text Source text
	 * @param string $targetLocale Target locale
	 * @param string $sourceLocale Source locale
	 * @return array<string, string> Engine => Translation
	 */
	public function suggest(string $text, string $targetLocale, string $sourceLocale = 'en'): array {
		if (empty($text)) {
			return [];
		}

		$targetLang = $this->extractLanguageCode($targetLocale);
		$sourceLang = $this->extractLanguageCode($sourceLocale);

		try {
			$translator = $this->getTranslator();

			return $translator->suggest($text, $targetLang, $sourceLang);
		} catch (Exception $e) {
			Log::warning('I18nTranslatorService: Suggestions failed - ' . $e->getMessage());

			return [];
		}
	}

	/**
	 * Find matching translations from PO glossary
	 *
	 * @param string $text Text to find matches for
	 * @param string $targetLocale Target locale
	 * @return array<array<string, mixed>>
	 */
	public function findGlossaryMatches(string $text, string $targetLocale): array {
		$matches = [];

		try {
			/** @var \Translate\Model\Table\TranslateTermsTable $termsTable */
			$termsTable = $this->fetchTable('Translate.TranslateTerms');
			/** @var \Translate\Model\Table\TranslateStringsTable $stringsTable */
			$stringsTable = $this->fetchTable('Translate.TranslateStrings');

			// Find exact matches first
			$exactMatch = $stringsTable->find()
				->where(['name' => $text])
				->first();

			if ($exactMatch) {
				$term = $termsTable->find()
					->contain(['TranslateLocales'])
					->where([
						'translate_string_id' => $exactMatch->id,
						'TranslateLocales.locale' => $targetLocale,
					])
					->first();

				if ($term) {
					$matches[] = [
						'type' => 'exact',
						'source' => $text,
						'translation' => $term->content,
						'confidence' => 1.0,
					];
				}
			}

			// Find partial matches (words/phrases within text)
			$words = $this->extractSignificantWords($text);

			foreach ($words as $word) {
				$stringMatches = $stringsTable->find()
					->where(['name LIKE' => '%' . $word . '%'])
					->limit(5)
					->toArray();

				foreach ($stringMatches as $string) {
					$term = $termsTable->find()
						->contain(['TranslateLocales'])
						->where([
							'translate_string_id' => $string->id,
							'TranslateLocales.locale' => $targetLocale,
						])
						->first();

					if ($term) {
						$matches[] = [
							'type' => 'partial',
							'source' => $string->name,
							'translation' => $term->content,
							'matched_word' => $word,
							'confidence' => 0.5,
						];
					}
				}
			}
		} catch (Exception $e) {
			// Translate tables might not exist
			Log::debug('I18nTranslatorService: Glossary lookup failed - ' . $e->getMessage());
		}

		// Remove duplicates and sort by confidence
		$unique = [];
		foreach ($matches as $match) {
			$key = $match['source'] . '|' . $match['translation'];
			if (!isset($unique[$key]) || $unique[$key]['confidence'] < $match['confidence']) {
				$unique[$key] = $match;
			}
		}

		usort($unique, fn ($a, $b) => $b['confidence'] <=> $a['confidence']);

		return array_slice($unique, 0, 10);
	}

	/**
	 * Get the translator instance
	 *
	 * @return \Translate\Translator\Translator
	 */
	protected function getTranslator(): Translator {
		if ($this->translator === null) {
			$this->translator = new Translator();
		}

		return $this->translator;
	}

	/**
	 * Extract language code from locale
	 *
	 * @param string $locale Locale (e.g., 'de_DE', 'en_US', 'de')
	 * @return string Language code (e.g., 'de', 'en')
	 */
	protected function extractLanguageCode(string $locale): string {
		if (str_contains($locale, '_')) {
			return substr($locale, 0, 2);
		}

		return $locale;
	}

	/**
	 * Extract significant words from text for glossary matching
	 *
	 * @param string $text Input text
	 * @return array<string>
	 */
	protected function extractSignificantWords(string $text): array {
		// Remove common words and short words
		$stopWords = [
			'the', 'a', 'an', 'and', 'or', 'but', 'is', 'are', 'was', 'were',
			'be', 'been', 'being',
			'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should',
			'may', 'might',
			'must', 'shall', 'can', 'for', 'of', 'to', 'in', 'on', 'at', 'by',
			'with', 'from', 'as', 'into',
			'der', 'die', 'das', 'den', 'dem', 'des', 'ein', 'eine', 'einer', 'eines',
			'einem', 'einen',
			'und', 'oder', 'aber', 'ist', 'sind', 'war', 'waren', 'sein', 'haben', 'hat',
			'hatte', 'hatten',
			'werden', 'wird', 'wurde', 'wurden', 'für', 'von', 'zu', 'in', 'an', 'auf',
			'bei', 'mit', 'aus',
		];

		$words = preg_split('/[\s,.:;!?()[\]{}]+/', strtolower($text));
		if ($words === false) {
			return [];
		}
		$words = array_filter($words, function ($word) use ($stopWords): bool {
			return strlen($word) > 3 && !in_array($word, $stopWords, true);
		});

		return array_values(array_unique($words));
	}

}
