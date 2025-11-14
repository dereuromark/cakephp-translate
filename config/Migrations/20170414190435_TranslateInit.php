<?php

use Phinx\Migration\AbstractMigration;

class TranslateInit extends AbstractMigration {

	/**
	 * @inheritDoc
	 */
	public function change() {
		$table = $this->table('translate_projects', ['signed' => false]);
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
			->addIndex(['name'], ['unique' => true])
			->create();

		$table = $this->table('translate_languages', ['signed' => false]);
		$table
			->addColumn('translate_project_id', 'integer', [
				'default' => null,
				'limit' => 10,
				'null' => false,
				'signed' => false,
			])
			->addColumn('language_id', 'integer', [
				'default' => null,
				'limit' => 10,
				'null' => true,
				'signed' => false,
			])
			->addColumn('name', 'string', [
				'default' => null,
				'limit' => 12,
				'null' => false,
			])
			->addColumn('locale', 'string', [
				'default' => null,
				'limit' => 10,
				'null' => false,
			])
			->addColumn('iso2', 'string', [
				'default' => null,
				'limit' => 2,
				'null' => true,
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
			->addIndex(['translate_project_id', 'locale'], ['unique' => true])
			->addIndex(['translate_project_id'])
			->addIndex(['iso2'])
			->addForeignKey('translate_project_id', 'translate_projects', 'id', [
				'delete' => 'CASCADE',
				'update' => 'NO_ACTION',
			])
			->create();

		$table = $this->table('translate_domains', ['signed' => false]);
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
				'signed' => false,
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
			->addIndex(['translate_project_id', 'name'], ['unique' => true])
			->addIndex(['translate_project_id'])
			->addForeignKey('translate_project_id', 'translate_projects', 'id', [
				'delete' => 'CASCADE',
				'update' => 'NO_ACTION',
			])
			->create();

		$table = $this->table('translate_strings', ['signed' => false]);
		$table
			->addColumn('context', 'string', [
				'default' => null,
				'limit' => 250,
				'null' => true,
				//'collate' => 'utf8_bin',
			])
			->addColumn('name', 'text', [
				'default' => null,
				'limit' => null,
				'null' => false,
				//'collate' => 'utf8_bin',
			])
			->addColumn('plural', 'text', [
				'default' => null,
				'limit' => null,
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
			->addColumn('translate_domain_id', 'integer', [
				'default' => null,
				'limit' => 10,
				'null' => false,
				'signed' => false,
			])
			->addColumn('user_id', 'integer', [
				'default' => null,
				'limit' => 10,
				'null' => true,
				'signed' => false,
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
			->addIndex(['translate_domain_id'])
			//->addIndex(['context'])
			->addForeignKey('translate_domain_id', 'translate_domains', 'id', [
				'delete' => 'CASCADE',
				'update' => 'NO_ACTION',
			])
			->create();

		$table = $this->table('translate_terms', ['signed' => false]);
		$table
			->addColumn('translate_string_id', 'integer', [
				'default' => null,
				'limit' => 10,
				'null' => false,
				'signed' => false,
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
				'signed' => false,
			])
			->addColumn('user_id', 'integer', [
				'default' => null,
				'limit' => 10,
				'null' => true,
				'signed' => false,
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
				'signed' => false,
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
			->addIndex(['translate_string_id', 'translate_language_id'], ['unique' => true])
			->addForeignKey('translate_string_id', 'translate_strings', 'id', [
				'delete' => 'CASCADE',
				'update' => 'NO_ACTION',
			])
			->addForeignKey('translate_language_id', 'translate_languages', 'id', [
				'delete' => 'CASCADE',
				'update' => 'NO_ACTION',
			])
			->create();

		$sql = <<<SQL
ALTER TABLE `translate_strings` CHANGE `name` `name` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL;
ALTER TABLE `translate_strings` CHANGE `context` `context` VARCHAR( 250 ) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL;
SQL;
		$this->query($sql);
	}

}
