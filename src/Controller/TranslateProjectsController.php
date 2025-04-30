<?php

namespace Translate\Controller;

/**
 * @property \Translate\Model\Table\TranslateDomainsTable $TranslateDomains
 * @property \Translate\Model\Table\TranslateLanguagesTable $TranslateLanguages
 * @property \Translate\Controller\Component\TranslationComponent $Translation
 */
class TranslateProjectsController extends TranslateAppController {

	/**
	 * @var string|null
	 */
	protected ?string $defaultTable = 'Translate.TranslateProjects';

	/**
	 * @return \Cake\Http\Response
	 */
	public function switchProject() {
		$projectId = (int)$this->request->getData('project_switch');
		$translateProject = $this->TranslateProjects->get($projectId);

		$this->request->getSession()->write('TranslateProject.id', $translateProject->id);
		$this->Flash->success(__d('translate', 'Project switched'));

		return $this->Common->autoRedirect(['controller' => 'Translate', 'action' => 'index']);
	}

}
