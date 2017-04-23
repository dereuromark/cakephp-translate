<?php

use Phinx\Migration\AbstractMigration;

class TranslateInit extends AbstractMigration {

	/**
	 * @inheritDoc
	 */
	public function change() {
		$table = $this->table('translate_projects');
		$table
			->addColumn('name', 'string', [
				'default' => null,
				'limit' => 60,
				'null' => false,
			])
			->addColumn('type', 'integer', [
				'default' => 0,
				'limit' => 2,
				'null' => false,
			])
			->addColumn('default', 'boolean', [
				'default' => 0,
				'limit' => null,
				'null' => false,
			])
			->addColumn('status', 'integer', [
				'default' => 0,
				'limit' => 2,
				'null' => false,
			])
			->addColumn('created', 'datetime', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('modified', 'datetime', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->create();

		$table = $this->table('translate_languages');
		$table
			->addColumn('translate_project_id', 'integer', [
				'default' => null,
				'limit' => 10,
				'null' => false,
			])
			->addColumn('language_id', 'integer', [
				'default' => null,
				'limit' => 10,
				'null' => true,
			])
			->addColumn('name', 'string', [
				'default' => null,
				'limit' => 12,
				'null' => false,
			])
			->addColumn('iso2', 'string', [
				'default' => null,
				'limit' => 2,
				'null' => false,
			])
			->addColumn('locale', 'string', [
				'default' => null,
				'limit' => 10,
				'null' => false,
			])
			->addColumn('active', 'boolean', [
				'default' => 1,
				'limit' => null,
				'null' => false,
			])
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
			->create();

		$table = $this->table('translate_groups');
		$table
			->addColumn('name', 'string', [
				'default' => null,
				'limit' => 60,
				'null' => false,
			])
			->addColumn('translate_project_id', 'integer', [
				'default' => null,
				'limit' => 10,
				'null' => false,
			])
			->addColumn('active', 'boolean', [
				'default' => 0,
				'limit' => null,
				'null' => false,
			])
			->addColumn('prio', 'integer', [
				'default' => 0,
				'limit' => 10,
				'null' => false,
			])
			->addColumn('path', 'string', [
				'default' => null,
				'limit' => 255,
				'null' => true,
			])
			->addColumn('created', 'datetime', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('modified', 'datetime', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->create();

		$table = $this->table('translate_strings');
		$table
			->addColumn('name', 'text', [
				'default' => null,
				'limit' => null,
				'null' => false,
			])
			->addColumn('plural', 'text', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('context', 'string', [
				'default' => null,
				'limit' => 250,
				'null' => true,
			])
			->addColumn('comment', 'text', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('flags', 'string', [
				'default' => null,
				'limit' => 250,
				'null' => true,
			])
			->addColumn('references', 'text', [
				'comment' => 'with file and code line',
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('translate_group_id', 'integer', [
				'default' => null,
				'limit' => 10,
				'null' => false,
			])
			->addColumn('user_id', 'integer', [
				'default' => null,
				'limit' => 10,
				'null' => true,
			])
			->addColumn('active', 'boolean', [
				'default' => 0,
				'limit' => null,
				'null' => false,
			])
			->addColumn('is_html', 'boolean', [
				'default' => 0,
				'limit' => null,
				'null' => false,
			])
			->addColumn('last_import', 'date', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('created', 'datetime', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('modified', 'datetime', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->create();

		$table = $this->table('translate_terms');
		$table
			->addColumn('translate_string_id', 'integer', [
				'default' => null,
				'limit' => 10,
				'null' => false,
			])
			->addColumn('content', 'text', [
				'default' => null,
				'limit' => null,
				'null' => false,
			])
			->addColumn('plural_2', 'string', [
				'default' => null,
				'limit' => 250,
				'null' => true,
			])
			->addColumn('comment', 'string', [
				'default' => null,
				'limit' => 255,
				'null' => true,
			])
			->addColumn('translate_language_id', 'integer', [
				'default' => null,
				'limit' => 10,
				'null' => false,
			])
			->addColumn('user_id', 'integer', [
				'default' => null,
				'limit' => 10,
				'null' => true,
			])
			->addColumn('confirmed', 'boolean', [
				'default' => 0,
				'limit' => null,
				'null' => false,
			])
			->addColumn('confirmed_by', 'integer', [
				'default' => null,
				'limit' => 10,
				'null' => true,
			])
			->addColumn('created', 'datetime', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('modified', 'datetime', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->create();
	}

}
