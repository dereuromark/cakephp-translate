<?php

namespace Translate\Controller\Component;

use Cake\Controller\Component;
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

}
