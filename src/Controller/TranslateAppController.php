<?php

namespace Translate\Controller;

use App\Controller\AppController;
use BootstrapUI\View\Helper\FormHelper;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Exception\ForbiddenException;
use Cake\I18n\I18n;
use Cake\Log\Log;
use Closure;
use Throwable;

/**
 * Authorization for the admin namespace.
 *
 * The admin UI of this plugin can write to disk, mutate the i18n catalog,
 * generate migrations and (in debug mode) edit source files — operational
 * damage if exposed. The default policy is **deny** for any request whose
 * routing prefix is `Admin`: the host application MUST set
 * `Translate.adminAccess` to a `Closure` that receives the current request
 * and returns literal `true` to grant access. Anything else (unset,
 * non-Closure, returns false, returns a truthy non-bool, or throws) yields
 * a 403.
 *
 * ```php
 * Configure::write('Translate.adminAccess', function (\Cake\Http\ServerRequest $request): bool {
 *     $identity = $request->getAttribute('identity');
 *     return $identity !== null && in_array('admin', (array)$identity->roles, true);
 * });
 * ```
 *
 * @property \Translate\Model\Table\TranslateProjectsTable $TranslateProjects
 * @property \Translate\Controller\Component\TranslationComponent $Translation
 */
class TranslateAppController extends AppController {

	/**
	 * Admin actions that legitimately require dynamic form-field names (multi-row
	 * batch saves, variable column counts, etc.). FormProtection token validation
	 * is suppressed *only* for these specific {Controller => [action, ...]} pairs;
	 * every other admin action runs with normal FormProtection (Issue #7).
	 *
	 * @var array<string, array<string>>
	 */
	protected const FORM_PROTECTION_UNLOCKED = [
		'TranslateStrings' => ['index', 'translate', 'extract', 'dump', 'runExtract', 'displayReference'],
		'TranslateBehavior' => ['generate'],
		'TranslateLocales' => ['toLocale'],
		'I18nEntries' => ['index'],
		'TranslateApiTranslations' => ['index'],
	];

	/**
	 * @throws \Exception
	 * @return void
	 */
	public function initialize(): void {
		parent::initialize();

		// Set translation manager locale after parent initialize
		$locale = $this->request->getSession()->read('Config.language');
		if ($locale && $locale !== Configure::read('Config.language')) {
			I18n::setLocale($locale);
		}

		$this->loadComponent('Translate.Translation');

		$this->viewBuilder()->addHelper('Translate.Translation');
		$this->viewBuilder()->addHelper('Translate.Icon');

		if (!$this->components()->has('Flash')) {
			$this->loadComponent('Flash');
		}
	}

	/**
	 * @param \Cake\Event\EventInterface<\Cake\Controller\Controller> $event
	 *
	 * @throws \Cake\Http\Exception\ForbiddenException When admin access is denied or unconfigured.
	 *
	 * @return void
	 */
	public function beforeFilter(EventInterface $event): void {
		parent::beforeFilter($event);

		$prefix = $this->request->getParam('prefix');
		$controller = $this->request->getParam('controller');
		$action = $this->request->getParam('action');

		if ($prefix === 'Admin') {
			$this->enforceAdminAccess();

			// Narrow FormProtection to only the dynamic-form actions that genuinely need it
			// (Issue #7). Every other admin action runs with normal token validation.
			if ($this->components()->has('FormProtection')) {
				$unlockedActions = static::FORM_PROTECTION_UNLOCKED[$controller] ?? [];
				if (in_array($action, $unlockedActions, true)) {
					/** @var \Cake\Controller\Component\FormProtectionComponent $formProtection */
					$formProtection = $this->components()->get('FormProtection');
					$formProtection->setConfig('validate', false);
				}
			}
		}

		// Apply language from session, or default to English for translation manager
		$locale = $this->request->getSession()->read('Config.language');
		if (!$locale) {
			$locale = 'en_US'; // Default translation manager interface to English
		}
		I18n::setLocale($locale);
		$this->request = $this->request->withAttribute('locale', $locale);

		// Auto-select default project if none selected (except for homepage and projects controller)
		$isHomepage = ($controller === 'Translate' && $action === 'index');
		$isProjectsController = ($controller === 'TranslateProjects');

		if (!$isHomepage && !$isProjectsController && !$this->request->getSession()->check('TranslateProject.id')) {
			$translateProjects = $this->fetchTable('Translate.TranslateProjects');
			$id = $translateProjects->getDefaultProjectId();
			if ($id) {
				$this->request->getSession()->write('TranslateProject.id', $id);
			}
		}
	}

	/**
	 * Default-deny gate for admin actions.
	 *
	 * @throws \Cake\Http\Exception\ForbiddenException
	 * @return void
	 */
	protected function enforceAdminAccess(): void {
		// Coexist with cakephp/authorization: the gate IS the authorization decision
		// for these controllers, so silence the policy check.
		if ($this->components()->has('Authorization') && method_exists($this->components()->get('Authorization'), 'skipAuthorization')) {
			$this->components()->get('Authorization')->skipAuthorization();
		}

		$gate = Configure::read('Translate.adminAccess');
		if (!($gate instanceof Closure)) {
			throw new ForbiddenException(__d(
				'translate',
				'Translate admin backend is not configured. Set Translate.adminAccess to a Closure that returns true for permitted callers.',
			));
		}

		try {
			$allowed = $gate($this->request) === true;
		} catch (ForbiddenException $e) {
			throw $e;
		} catch (Throwable $e) {
			Log::warning(sprintf('Translate.adminAccess threw %s: %s', $e::class, $e->getMessage()));

			throw new ForbiddenException(__d('translate', 'Translate admin access denied.'), $e->getCode(), $e);
		}

		if (!$allowed) {
			throw new ForbiddenException(__d('translate', 'Translate admin access denied.'));
		}
	}

	/**
	 * @param \Cake\Event\EventInterface<\Cake\Controller\Controller> $event
	 * @return void
	 */
	public function beforeRender(EventInterface $event): void {
		// Only set default layout if one hasn't been explicitly set
		if ($this->viewBuilder()->getLayout() === null) {
			$layout = Configure::read('Translate.layout', 'Translate.simple');
			$this->viewBuilder()->setLayout($layout);
		}

		$map = Configure::read('Translate.iconMap');
		if ($map) {
			$map += (array)Configure::read('Icon.map');
			Configure::write('Icon.map', $map);
		}

		if (class_exists(FormHelper::class)) {
			$this->viewBuilder()->addHelper('BootstrapUi.Form');
		}

		// Make current project available to all views
		$projectId = $this->request->getSession()->read('TranslateProject.id');
		if ($projectId) {
			$translateProjects = $this->fetchTable('Translate.TranslateProjects');
			$currentProject = $translateProjects->find()
				->where(['id' => $projectId])
				->first();
			$this->set('currentProject', $currentProject);
		} else {
			$this->set('currentProject', null);
		}
	}

}
