<?php

namespace Translate\Controller\Component;

use Cake\ORM\TableRegistry;
use Shim\Controller\Component\Component;

class TranslationComponent extends Component {

	/**
	 * @return int|null
	 */
	public function currentProjectId() {
		$id = $this->request->session()->read('TranslateProject.id');
		if ($id === null) {
			/** @var \Translate\Model\Table\TranslateProjectsTable $TranslationProjects */
			$TranslationProjects = TableRegistry::get('Translate.TranslateProjects');
			$id = $TranslationProjects->getDefaultProjectId();

			$this->request->session()->write('TranslateProject.id', $id);
		}

		return $id;
	}

}
