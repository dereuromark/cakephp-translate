<?php

use Migrations\BaseMigration;

class TranslateUnused extends BaseMigration {

	/**
	 * @inheritDoc
	 */
	public function change() {
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
