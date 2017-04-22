<?php

use Phinx\Migration\AbstractMigration;

class TranslateLanguages extends AbstractMigration {

	/**
	 * @inheritDoc
	 */
	public function change() {
		$table = $this->table('translate_languages');
		$table
			->addColumn('base', 'boolean', [
				'default' => 0,
				'limit' => null,
				'null' => false,
			])
			->addColumn('primary', 'boolean', [
				'default' => 0,
				'limit' => null,
				'null' => false,
			])
			->update();
	}

}
