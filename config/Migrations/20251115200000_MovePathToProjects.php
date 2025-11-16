<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

/**
 * Move path field from translate_domains to translate_projects
 */
class MovePathToProjects extends AbstractMigration {

	/**
	 * Change Method.
	 *
	 * @return void
	 */
	public function change(): void {
		// Add path column to translate_projects
		$table = $this->table('translate_projects');
		$table->addColumn('path', 'string', [
			'default' => null,
			'limit' => 255,
			'null' => true,
			'after' => 'status',
		])
		->update();

		// Remove path column from translate_domains
		$table = $this->table('translate_domains');
		$table->removeColumn('path')
			->update();
	}

}
