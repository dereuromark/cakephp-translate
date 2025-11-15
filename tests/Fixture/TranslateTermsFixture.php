<?php

namespace Translate\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * TranslateTermsFixture
 */
class TranslateTermsFixture extends TestFixture {

	/**
	 * Fields
	 *
	 * @var array
	 */
	// phpcs:disable
	public array $fields = [
		'id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'autoIncrement' => true, 'precision' => null],
		'translate_string_id' => ['type' => 'integer', 'length' => 10, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
		'content' => ['type' => 'text', 'length' => null, 'null' => false, 'default' => null, 'collate' => 'utf8mb4_unicode_ci', 'comment' => '', 'precision' => null],
		'plural_2' => ['type' => 'string', 'length' => 250, 'null' => true, 'default' => null, 'collate' => 'utf8mb4_unicode_ci'],
		'comment' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'utf8mb4_unicode_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
		'translate_locale_id' => ['type' => 'integer', 'length' => 10, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
		'user_id' => ['type' => 'integer', 'length' => 10, 'null' => true, 'default' => null, 'comment' => 'submitted by', 'precision' => null, 'fixed' => null],
		'confirmed' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => '0', 'comment' => '', 'precision' => null],
		'confirmed_by' => ['type' => 'integer', 'length' => 10, 'null' => true, 'default' => null, 'collate' => 'utf8mb4_unicode_ci', 'fixed' => null],
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
			'translate_string_id' => 1,
			'content' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
			'plural_2' => 'Lorem ipsum dolor sit amet',
			'comment' => 'Lorem ipsum dolor sit amet',
			'translate_locale_id' => 1,
			'user_id' => 1,
			'confirmed' => 1,
			'confirmed_by' => null,
			'created' => '2017-04-15 01:23:08',
			'modified' => '2017-04-15 01:23:08',
		],
	];

}
