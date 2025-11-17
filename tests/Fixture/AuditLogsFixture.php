<?php

namespace Translate\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * AuditLogsFixture
 *
 * Fixture for AuditStash plugin audit_logs table
 * This is only needed when AuditStash is installed for testing
 */
class AuditLogsFixture extends TestFixture {

	/**
	 * Table name
	 *
	 * @var string
	 */
	public string $table = 'audit_logs';

	/**
	 * Fields
	 *
	 * @var array
	 */
	public array $fields = [
		'id' => ['type' => 'uuid', 'length' => null, 'null' => false, 'default' => null],
		'transaction' => ['type' => 'uuid', 'length' => null, 'null' => false, 'default' => null],
		'type' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null],
		'primary_key' => ['type' => 'uuid', 'length' => null, 'null' => false, 'default' => null],
		'source' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null],
		'parent_source' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null],
		'original' => ['type' => 'text', 'length' => null, 'null' => true, 'default' => null],
		'changed' => ['type' => 'text', 'length' => null, 'null' => true, 'default' => null],
		'meta' => ['type' => 'text', 'length' => null, 'null' => true, 'default' => null],
		'created' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => null],
		'_constraints' => [
			'primary' => ['type' => 'primary', 'columns' => ['id']],
		],
	];

	/**
	 * Records
	 *
	 * @var array
	 */
	public array $records = [];

}
