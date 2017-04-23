<?php

use Phinx\Migration\AbstractMigration;

class TranslateCache extends AbstractMigration {

	/**
	 * @inheritDoc
	 */
	public function change() {
		$table = $this->table('translate_api_translations');
		$table
			->addColumn('key', 'text', [
				'default' => null,
				'null' => false,
			])
			->addColumn('value', 'text', [
				'default' => null,
				'null' => true,
			])
			->addColumn('from', 'string', [
				'default' => null,
				'limit' => 6,
				'null' => false,
			])
			->addColumn('to', 'string', [
				'default' => null,
				'limit' => 6,
				'null' => false,
			])
			->addColumn('engine', 'string', [
				'default' => null,
				'limit' => 60,
				'null' => false,
			])
			->addColumn('created', 'datetime', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->create();
	}

}
