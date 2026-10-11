<?php

namespace Translate\View\Cell;

use Cake\Core\Configure;
use Cake\View\Cell;

class LanguageSwitcherCell extends Cell {

	/**
	 * @return void
	 */
	public function initialize(): void {
		$this->viewBuilder()->addHelper('Url');
	}

	/**
	 * @param int|null $projectId Explicit project, or the current interface's selection.
	 * @return void
	 */
	public function display(?int $projectId = null): void {
		if ($projectId === null) {
			$key = $this->request->getParam('prefix') === 'Admin' ? 'TranslateProject.id' : 'TranslatePublicProject.id';
			$projectId = $this->request->getSession()->read($key);
		}
		$activeLanguages = [];
		if ($projectId) {
			$activeLanguages = $this->fetchTable('Translate.TranslateLocales')->find()
				->where(['active' => true, 'translate_project_id' => $projectId])
				->orderBy(['name' => 'ASC'])
				->all()
				->toArray();
		}
		$currentLocale = $this->request->getSession()->read('Config.language') ?: Configure::read('App.defaultLocale') ?: 'en_US';
		$this->set(compact('activeLanguages', 'currentLocale'));
	}

}
