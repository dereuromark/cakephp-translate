<?php

namespace Translate\Test\TestCase\Model\Behavior;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * Test that AuditLog behavior is properly integrated when AuditStash plugin is available
 */
class AuditLogIntegrationTest extends TestCase {

	/**
	 * Fixtures
	 *
	 * @var list<string>
	 */
	protected array $fixtures = [
		'plugin.Translate.AuditLogs',
		'plugin.Translate.TranslateProjects',
		'plugin.Translate.TranslateDomains',
		'plugin.Translate.TranslateLocales',
		'plugin.Translate.TranslateStrings',
		'plugin.Translate.TranslateTerms',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		// Enable audit logging for this specific test
		Configure::write('Translate.disableAuditLog', false);

		// Clear table registry to force reload with audit logging enabled
		TableRegistry::getTableLocator()->clear();
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown(): void {
		parent::tearDown();

		// Re-disable audit logging after test
		Configure::write('Translate.disableAuditLog', true);

		// Clear table registry
		TableRegistry::getTableLocator()->clear();
	}

	/**
	 * Test that TranslateStrings table loads AuditLog behavior when AuditStash is available
	 *
	 * @return void
	 */
	public function testTranslateStringsHasAuditLogBehavior() {
		$table = TableRegistry::getTableLocator()->get('Translate.TranslateStrings');

		// Verify the behavior is loaded if AuditStash plugin is available
		if (class_exists('\AuditStash\AuditStashPlugin')) {
			$this->assertTrue($table->hasBehavior('AuditLog'), 'TranslateStrings should have AuditLog behavior when AuditStash is installed');
		} else {
			$this->assertFalse($table->hasBehavior('AuditLog'), 'TranslateStrings should not have AuditLog behavior when AuditStash is not installed');
		}
	}

	/**
	 * Test that TranslateTerms table loads AuditLog behavior when AuditStash is available
	 *
	 * @return void
	 */
	public function testTranslateTermsHasAuditLogBehavior() {
		$table = TableRegistry::getTableLocator()->get('Translate.TranslateTerms');

		// Verify the behavior is loaded if AuditStash plugin is available
		if (class_exists('\AuditStash\AuditStashPlugin')) {
			$this->assertTrue($table->hasBehavior('AuditLog'), 'TranslateTerms should have AuditLog behavior when AuditStash is installed');
		} else {
			$this->assertFalse($table->hasBehavior('AuditLog'), 'TranslateTerms should not have AuditLog behavior when AuditStash is not installed');
		}
	}

	/**
	 * Test that AuditLog behavior is configured to exclude timestamp fields
	 *
	 * @return void
	 */
	public function testAuditLogBehaviorConfiguration() {
		if (!class_exists('\AuditStash\AuditStashPlugin')) {
			$this->markTestSkipped('AuditStash plugin is not installed');
		}

		$stringsTable = TableRegistry::getTableLocator()->get('Translate.TranslateStrings');
		$termsTable = TableRegistry::getTableLocator()->get('Translate.TranslateTerms');

		// Verify both tables have the behavior
		$this->assertTrue($stringsTable->hasBehavior('AuditLog'));
		$this->assertTrue($termsTable->hasBehavior('AuditLog'));

		// Get the behavior configuration
		$stringsBehavior = $stringsTable->getBehavior('AuditLog');
		$termsBehavior = $termsTable->getBehavior('AuditLog');

		// Verify the blacklist configuration
		$stringsConfig = $stringsBehavior->getConfig();
		$termsConfig = $termsBehavior->getConfig();

		$this->assertArrayHasKey('blacklist', $stringsConfig);
		$this->assertContains('modified', $stringsConfig['blacklist']);
		$this->assertContains('created', $stringsConfig['blacklist']);

		$this->assertArrayHasKey('blacklist', $termsConfig);
		$this->assertContains('modified', $termsConfig['blacklist']);
		$this->assertContains('created', $termsConfig['blacklist']);
	}

}
