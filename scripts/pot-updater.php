#!/usr/bin/env php
<?php
/**
 * POT Updater - Standalone tool for checking/updating POT files
 *
 * Usage: php pot-updater.php [options]
 *
 * Options:
 *   --dry-run          Compare and report differences without writing (default)
 *   --update           Actually update the POT file(s)
 *   --verbose, -v      Show detailed output including all strings
 *   --quiet, -q        Only output errors, suitable for CI
 *   --path=<path>      Custom paths to scan, comma-separated (default: src,templates)
 *   --output=<path>    Custom output path (default: resources/locales)
 *   --domain=<name>    Expected domain name (default: auto-detect from plugin name)
 *   --fail-on-diff     Exit with code 1 if differences found (for CI)
 *   --ignore-references  Ignore comment/reference differences
 *   --help, -h         Show this help message
 *
 * Examples:
 *   php vendor/dereuromark/cakephp-translate/scripts/pot-updater.php
 *   php vendor/dereuromark/cakephp-translate/scripts/pot-updater.php --update
 *   php vendor/dereuromark/cakephp-translate/scripts/pot-updater.php --dry-run --fail-on-diff
 *   php vendor/dereuromark/cakephp-translate/scripts/pot-updater.php --domain=queue
 *
 * @copyright Copyright (c) Mark Scherer
 * @license MIT
 */

// Find autoloader
$autoloaders = [
	// When running from vendor directory of another project
	dirname(__DIR__, 4) . '/vendor/autoload.php',
	// When running from plugin root during development
	dirname(__DIR__) . '/vendor/autoload.php',
];

$autoloaderFound = false;
foreach ($autoloaders as $autoloader) {
	if (file_exists($autoloader)) {
		require_once $autoloader;
		$autoloaderFound = true;
		break;
	}
}

if (!$autoloaderFound) {
	fwrite(STDERR, "Error: Could not find Composer autoloader.\n");
	fwrite(STDERR, "Make sure you have run 'composer install'.\n");
	exit(2);
}

use Translate\PotUpdater\PotUpdater;

// Show help if requested
if (in_array('--help', $argv, true) || in_array('-h', $argv, true)) {
	$help = <<<'HELP'
POT Updater - Standalone tool for checking/updating POT files

Usage: php pot-updater.php [options]

Options:
  --dry-run            Compare and report differences without writing (default)
  --update             Actually update the POT file(s)
  --verbose, -v        Show detailed output including all strings
  --quiet, -q          Only output errors, suitable for CI
  --path=<path>        Custom paths to scan, comma-separated (default: src,templates)
  --output=<path>      Custom output path (default: resources/locales)
  --domain=<name>      Expected domain name (default: auto-detect from plugin name)
  --fail-on-diff       Exit with code 1 if differences found (for CI)
  --ignore-references  Ignore comment/reference differences
  --help, -h           Show this help message

Examples:
  php vendor/dereuromark/cakephp-translate/scripts/pot-updater.php
  php vendor/dereuromark/cakephp-translate/scripts/pot-updater.php --update
  php vendor/dereuromark/cakephp-translate/scripts/pot-updater.php --dry-run --fail-on-diff

Exit Codes:
  0 - Success (POT file is up to date or was updated successfully)
  1 - Differences found (when using --fail-on-diff)
  2 - Error (could not read/write files, invalid options)

HELP;
	echo $help;
	exit(0);
}

// Determine plugin path (current working directory)
$pluginPath = getcwd();

if ($pluginPath === false) {
	fwrite(STDERR, "Error: Could not determine current working directory.\n");
	exit(2);
}

// Run the updater
$updater = new PotUpdater($pluginPath);
exit($updater->run($argv));
