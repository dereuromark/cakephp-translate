<?php

namespace Translate\Test\TestCase\PotUpdater;

use PHPUnit\Framework\TestCase;
use Translate\PotUpdater\PotComparator;

/**
 * @uses \Translate\Utility\PotComparator
 */
class PotComparatorTest extends TestCase {

	/**
	 * Test comparing identical strings
	 *
	 * @return void
	 */
	public function testCompareIdentical(): void {
		$strings = [
			'Hello' => [
				'msgid' => 'Hello',
				'msgid_plural' => null,
				'msgctxt' => null,
				'references' => ['src/file.php:1'],
				'comments' => [],
			],
		];

		$comparator = new PotComparator();
		$diff = $comparator->compare($strings, $strings);

		$this->assertEmpty($diff['added']);
		$this->assertEmpty($diff['removed']);
		$this->assertEmpty($diff['changed']);
		$this->assertCount(1, $diff['unchanged']);

		$this->assertFalse($comparator->hasDifferences($diff));
	}

	/**
	 * Test detecting added strings
	 *
	 * @return void
	 */
	public function testCompareAdded(): void {
		$existing = [];
		$current = [
			'New string' => [
				'msgid' => 'New string',
				'msgid_plural' => null,
				'msgctxt' => null,
				'references' => ['src/file.php:1'],
				'comments' => [],
			],
		];

		$comparator = new PotComparator();
		$diff = $comparator->compare($existing, $current);

		$this->assertCount(1, $diff['added']);
		$this->assertArrayHasKey('New string', $diff['added']);
		$this->assertEmpty($diff['removed']);
		$this->assertEmpty($diff['changed']);

		$this->assertTrue($comparator->hasDifferences($diff));
	}

	/**
	 * Test detecting removed strings
	 *
	 * @return void
	 */
	public function testCompareRemoved(): void {
		$existing = [
			'Old string' => [
				'msgid' => 'Old string',
				'msgid_plural' => null,
				'msgctxt' => null,
				'references' => ['src/file.php:1'],
				'comments' => [],
			],
		];
		$current = [];

		$comparator = new PotComparator();
		$diff = $comparator->compare($existing, $current);

		$this->assertEmpty($diff['added']);
		$this->assertCount(1, $diff['removed']);
		$this->assertArrayHasKey('Old string', $diff['removed']);

		$this->assertTrue($comparator->hasDifferences($diff));
	}

	/**
	 * Test detecting changed references
	 *
	 * @return void
	 */
	public function testCompareChangedReferences(): void {
		$existing = [
			'Hello' => [
				'msgid' => 'Hello',
				'msgid_plural' => null,
				'msgctxt' => null,
				'references' => ['src/old.php:1'],
				'comments' => [],
			],
		];
		$current = [
			'Hello' => [
				'msgid' => 'Hello',
				'msgid_plural' => null,
				'msgctxt' => null,
				'references' => ['src/new.php:1'],
				'comments' => [],
			],
		];

		$comparator = new PotComparator();
		$diff = $comparator->compare($existing, $current);

		$this->assertEmpty($diff['added']);
		$this->assertEmpty($diff['removed']);
		$this->assertCount(1, $diff['changed']);
		$this->assertArrayHasKey('Hello', $diff['changed']);

		$this->assertTrue($comparator->hasDifferences($diff));
		$this->assertFalse($comparator->hasDifferences($diff, true)); // ignore references
	}

	/**
	 * Test detecting changed plural form
	 *
	 * @return void
	 */
	public function testCompareChangedPlural(): void {
		$existing = [
			'One item' => [
				'msgid' => 'One item',
				'msgid_plural' => 'Many items',
				'msgctxt' => null,
				'references' => ['src/file.php:1'],
				'comments' => [],
			],
		];
		$current = [
			'One item' => [
				'msgid' => 'One item',
				'msgid_plural' => '{0} items',
				'msgctxt' => null,
				'references' => ['src/file.php:1'],
				'comments' => [],
			],
		];

		$comparator = new PotComparator();
		$diff = $comparator->compare($existing, $current);

		$this->assertCount(1, $diff['changed']);
		$this->assertTrue($comparator->hasDifferences($diff));
	}

	/**
	 * Test summary statistics
	 *
	 * @return void
	 */
	public function testGetSummary(): void {
		$existing = [
			'Old' => ['msgid' => 'Old', 'msgid_plural' => null, 'msgctxt' => null, 'references' => [], 'comments' => []],
			'Same' => ['msgid' => 'Same', 'msgid_plural' => null, 'msgctxt' => null, 'references' => ['a:1'], 'comments' => []],
			'Changed' => ['msgid' => 'Changed', 'msgid_plural' => null, 'msgctxt' => null, 'references' => ['a:1'], 'comments' => []],
		];
		$current = [
			'New' => ['msgid' => 'New', 'msgid_plural' => null, 'msgctxt' => null, 'references' => [], 'comments' => []],
			'Same' => ['msgid' => 'Same', 'msgid_plural' => null, 'msgctxt' => null, 'references' => ['a:1'], 'comments' => []],
			'Changed' => ['msgid' => 'Changed', 'msgid_plural' => null, 'msgctxt' => null, 'references' => ['b:2'], 'comments' => []],
		];

		$comparator = new PotComparator();
		$diff = $comparator->compare($existing, $current);
		$summary = $comparator->getSummary($diff);

		$this->assertSame(1, $summary['added']);
		$this->assertSame(1, $summary['removed']);
		$this->assertSame(1, $summary['changed']);
		$this->assertSame(1, $summary['unchanged']);
		$this->assertSame(3, $summary['total_existing']);
		$this->assertSame(3, $summary['total_current']);
	}

}
