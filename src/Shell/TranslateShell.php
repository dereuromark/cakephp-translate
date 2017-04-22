<?php
namespace Translate\Shell;

use Cake\Console\Shell;

class TranslateShell extends Shell {

	/**
	 * @var string
	 */
	public $modelClass = 'Translate.TranslateStrings';

	/**
	 * Output some basic usage Info.
	 *
	 * @return void
	 */
	public function help() {
		$this->out('CakePHP Translate Plugin:');
		$this->out();

		$this->out('Run `bin/cake i18n extract` first to create POT files.');
		$this->out('Import them then via `bin/cake translate import`.');
		$this->out('When done translating, you can export them via `bin/cake translate export`.');

		$this->hr();
	}

	/**
	 * Display help for this console.
	 *
	 * @return \Cake\Console\ConsoleOptionParser
	 */
	public function getOptionParser() {
		$consoleOptionParser = parent::getOptionParser();

		$consoleOptionParser->addSubcommand('help', [
			'help' => 'Display some help.',
		]);

		return $consoleOptionParser;
	}

}
