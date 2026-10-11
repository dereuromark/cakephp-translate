<?php

namespace Translate\Filesystem;

use Cake\Core\Configure;
use Exception;
use Translate\Parser\PoParser;

class Dumper {

	/**
	 * @param array<\Translate\Model\Entity\TranslateTerm> $translations
	 * @param string $domain
	 * @param string $lang
	 * @param string|null $folder
	 *
	 * @return bool
	 */
	public function dump(array $translations, string $domain, string $lang, ?string $folder = null) {
		if ($folder === null) {
			$folder = LOCALE;
		}
		$folder = rtrim($folder, '/\\') . DS . $lang . DS;
		if (!is_dir($folder) && !@mkdir($folder, 0770, true) && !is_dir($folder)) {
			return false;
		}
		$file = $folder . $domain . '.po';

		return $this->_dump($translations, $file);
	}

	/**
	 * @param array<\Translate\Model\Entity\TranslateTerm> $translations $translations
	 * @param string $file
	 *
	 * @return bool
	 */
	protected function _dump(array $translations, string $file) {
		$max = Configure::read('Translate.plurals') ?: 2;
		$pluralExpression = Configure::read('Translate.pluralExpression') ?: 'n != 1';
		$noComments = Configure::read('Translate.noComments') ?: false;

		$po = new PoParser();
		$headers = "MIME-Version: 1.0\nContent-Type: text/plain; charset=UTF-8\n"
			. "Content-Transfer-Encoding: 8bit\nPlural-Forms: nplurals=$max; plural=$pluralExpression;\n";

		$entries = [
			'' => [
				'msgid' => '',
				'msgstr' => $headers,
			],
		];
		foreach ($translations as $translation) {
			$entry = [
				'msgid' => $translation->translate_string->name,
				'msgstr' => (string)$translation->content,
			];

			// Handle plurals
			if ($translation->translate_string->plural !== null) {
				$entry['msgid_plural'] = $translation->translate_string->plural;
				// Writer expects msgstr as an array for plurals: [0 => singular, 1 => plural, ...]
				$pluralMsgstr = [(string)$translation->content];
				for ($i = 2; $i <= $max; $i++) {
					$pluralVersion = 'plural_' . $i;
					$pluralMsgstr[] = (string)$translation->get($pluralVersion);
				}
				$entry['msgstr'] = $pluralMsgstr;
			}

			// Handle context
			if ($translation->translate_string->context) {
				$entry['msgctxt'] = $translation->translate_string->context;
			}

			// Handle references, flags, and comments (unless noComments is set)
			if (!$noComments) {
				// References are stored as newline-separated strings
				if ($translation->translate_string->references) {
					$entry['references'] = array_filter(explode("\n", $translation->translate_string->references));
				}

				// Flags are stored as array
				if ($translation->translate_string->flags) {
					$entry['flags'] = $translation->translate_string->flags;
				}

				// Comments (translator comments)
				if (($translation->translate_string->comment ?? $translation->translate_string->comments)) {
					$entry['tcomment'] = $translation->translate_string->comment ?? $translation->translate_string->comments;
				}
			}

			$entries[] = $entry;
		}
		$po->setEntries($entries);

		try {
			$po->write($file);
		} catch (Exception $e) {
			return false;
		}

		return true;
	}

}
