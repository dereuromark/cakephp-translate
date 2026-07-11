<?php

namespace Translate\View\Cell;

use Cake\ORM\TableRegistry;
use Cake\View\Cell;

class LanguageSwitcherCell extends Cell {

	public function initialize(): void {
		$this->viewBuilder()->addHelper('Url');
	}

	public function display(int $projectId): void {
		$TranslateLocales = TableRegistry::getTableLocator()->get('Translate.TranslateLocales');
		$activeLanguages = $TranslateLocales->find()
			->where([
				'active' => true,
				'translate_project_id' => $projectId,
			])
			->orderBy(['name' => 'ASC'])
			->all()
			->toArray();
		$this->set(compact('activeLanguages'));
	}

}
