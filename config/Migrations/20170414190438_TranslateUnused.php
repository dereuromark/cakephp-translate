<?php

use Phinx\Migration\AbstractMigration;

class TranslateUnused extends AbstractMigration {

	/**
	 * @inheritDoc
	 */
	public function change() {
		/*
		$table = $this->table('translate_languages');
		$table->rename('translate_locales');
		*/

		$table = $this->table('translate_strings');
		$table
			->addColumn('skipped', 'boolean', [
				'default' => 0,
				'null' => false,
			])
			->addColumn('unused', 'boolean', [
				'default' => 0,
				'null' => false,
			])
			->addColumn('manual', 'boolean', [
				'default' => 0,
				'null' => false,
			])
			->update();
	}

}
