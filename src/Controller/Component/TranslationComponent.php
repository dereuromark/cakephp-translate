<?php

namespace Translate\Controller\Component;

use Cake\Controller\Component;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;

/**
 * @method \App\Controller\AppController getController()
 */
class TranslationComponent extends Component {

	/**
	 * @return int|null
	 */
	public function currentProjectId() {
		$id = $this->getController()->getRequest()->getSession()->read('TranslateProject.id');
		if ($id === null) {
			/** @var \Translate\Model\Table\TranslateProjectsTable $TranslationProjects */
			$TranslationProjects = TableRegistry::getTableLocator()->get('Translate.TranslateProjects');
			$id = $TranslationProjects->getDefaultProjectId();

			$this->getController()->getRequest()->getSession()->write('TranslateProject.id', $id);
		}

		return $id;
	}

	/**
	 * @return void
	 */
	public function hardReset(): void {
		/** @var \Cake\Database\Connection $connection */
		$connection = ConnectionManager::get('default');

		$tables = [
			'translate_domains',
			'translate_strings',
			'translate_languages',
			'translate_terms',
			'translate_api_translations',
		];

		$tableTruncates = 'TRUNCATE TABLE ' . implode(';' . PHP_EOL . 'TRUNCATE TABLE ', $tables) . ';';

		$sql = <<<SQL
SET FOREIGN_KEY_CHECKS = 0;

$tableTruncates

SET FOREIGN_KEY_CHECKS = 1;
SQL;
		$connection->execute($sql);
	}

}
