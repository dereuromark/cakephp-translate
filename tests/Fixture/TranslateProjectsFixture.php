<?php

namespace Translate\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;
use Translate\Model\Entity\TranslateProject;

/**
 * TranslateProjectsFixture
 */
class TranslateProjectsFixture extends TestFixture {

	/**
	 * Fields
	 *
	 * @var array
	 */
	// phpcs:disable
	public array $fields = [
		'id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'autoIncrement' => true, 'precision' => null],
		'name' => ['type' => 'string', 'length' => 60, 'null' => false, 'default' => null, 'collate' => 'utf8mb4_unicode_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
		'type' => ['type' => 'integer', 'length' => 2, 'unsigned' => false, 'null' => false, 'default' => '0', 'comment' => '', 'precision' => null, 'autoIncrement' => null],
		'default' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => '0', 'comment' => '', 'precision' => null],
		'status' => ['type' => 'integer', 'length' => 2, 'unsigned' => false, 'null' => false, 'default' => '0', 'comment' => '', 'precision' => null, 'autoIncrement' => null],
		'path' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'utf8mb4_unicode_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
		'created' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
		'modified' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
		'_constraints' => ['primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []]],
		'_options' => ['engine' => 'InnoDB', 'collation' => 'utf8mb4_unicode_ci'],
	];

	/**
	 * Records
	 *
	 * @var array
	 */
	public array $records = [
		[
			'name' => 'Lorem ipsum dolor sit amet',
			'type' => TranslateProject::TYPE_APP,
			'default' => 1,
			'status' => TranslateProject::STATUS_HIDDEN,
			'path' => 'tests',
			'created' => '2017-04-15 01:23:08',
			'modified' => '2017-04-15 01:23:08',
		],
	];

}
