<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/**
 * Rename translate_locales to translate_locales for better clarity
 * Since locales can represent multiple variants of the same language (de_DE, de_CH, etc.)
 */
class RenameLanguagesToLocales extends BaseMigration {

	/**
	 * Change Method.
	 *
	 * @return void
	 */
	public function change(): void {
		// Rename the main table
		$this->table('translate_languages')
			->rename('translate_locales')
			->update();

		// Rename foreign key column in translate_terms
		$this->table('translate_terms')
			->renameColumn('translate_language_id', 'translate_locale_id')
			->update();
	}

}
