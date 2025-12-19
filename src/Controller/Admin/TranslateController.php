<?php

namespace Translate\Controller\Admin;

use Cake\View\JsonView;
use DateTime;
use Exception;
use Translate\Controller\TranslateAppController;
use Translate\Lib\ConvertLib;
use Translate\Translator\Translator;

/**
 * @property \Translate\Model\Table\TranslateDomainsTable $TranslateDomains
 * @property \Translate\Model\Table\TranslateLocalesTable $TranslateLocales
 * @property \Translate\Controller\Component\TranslationComponent $Translation
 */
class TranslateController extends TranslateAppController {

	/**
	 * @var string|null
	 */
	protected ?string $defaultTable = 'Translate.TranslateDomains';

	/**
	 * Get alternate view classes that can be used in
	 * content-type negotiation.
	 *
	 * @return list<string>
	 */
	public function viewClasses(): array {
		return [JsonView::class];
	}

	/**
	 * Initial page / overview
	 *
	 * @return void
	 */
	public function index() {
		$id = $this->Translation->currentProjectId();

		$translateLocalesTable = $this->fetchTable('Translate.TranslateLocales');
		$languages = $translateLocalesTable->find('all')
			->where([
				'translate_project_id' => $id,
				'active' => true,
			])
			->toArray();

		$count = $id ? $this->TranslateDomains->statistics($id, $languages) : 0;
		$coverage = $id ? $this->TranslateDomains->TranslateStrings->coverage($id) : 0;
		$projectSwitchArray = $this->TranslateDomains->TranslateProjects->find('list')->toArray();

		// Calculate translated counts per locale for coverage table
		$translateStringsTable = $this->fetchTable('Translate.TranslateStrings');
		$totalStrings = is_array($count) ? $count['strings'] : 0;
		$localeStats = [];
		foreach ($languages as $language) {
			// Count strings that have a translation for this locale
			$translatedCount = $translateStringsTable->TranslateTerms->find()
				->where([
					'TranslateTerms.translate_locale_id' => $language->id,
					'TranslateTerms.content IS NOT' => null,
					'TranslateTerms.content !=' => '',
				])
				->innerJoinWith('TranslateStrings.TranslateDomains', function ($q) use ($id) {
					return $q->where([
						'TranslateDomains.translate_project_id' => $id,
						'TranslateDomains.active' => true,
					]);
				})
				->count();

			$localeStats[$language->locale] = [
				'translated' => $translatedCount,
				'total' => $totalStrings,
			];
		}

		// Get recent activity
		$recentStrings = [];
		$recentTerms = [];
		$auditLogs = [];
		$confirmationStats = [];
		$recentImports = [];
		$auditData = [];

		if ($id) {
			// Recent strings (last 10 added/modified)
			$recentStrings = $this->TranslateDomains->TranslateStrings
				->find()
				->contain(['TranslateDomains'])
				->where(['TranslateDomains.translate_project_id' => $id])
				->orderBy(['TranslateStrings.modified' => 'DESC'])
				->limit(10)
				->toArray();

			// Recent terms (last 10 translations)
			$recentTerms = $this->TranslateDomains->TranslateStrings->TranslateTerms
				->find()
				->contain([
					'TranslateStrings' => ['TranslateDomains'],
					'TranslateLocales',
				])
				->where(['TranslateDomains.translate_project_id' => $id])
				->orderBy(['TranslateTerms.modified' => 'DESC'])
				->limit(10)
				->toArray();

			// Get audit logs if AuditStash is available
			$auditData = [];
			if (class_exists('\AuditStash\Model\Table\AuditLogsTable')) {
				try {
					$auditLogsTable = $this->fetchTable('AuditStash.AuditLogs');
					$auditLogs = $auditLogsTable
						->find()
						->where([
							'source IN' => ['TranslateStrings', 'TranslateTerms'],
						])
						->orderBy(['created' => 'DESC'])
						->limit(15)
						->toArray();

					// Build audit data map for quick lookup (source => primary_key => logs)
					foreach ($auditLogs as $log) {
						$key = $log->source . '_' . $log->primary_key;
						if (!isset($auditData[$key])) {
							$auditData[$key] = [];
						}
						$auditData[$key][] = $log;
					}
				} catch (Exception $e) {
					// Table doesn't exist or error loading, skip audit logs
					$auditLogs = [];
				}
			}

			// Get confirmation statistics per locale (filtered by current project and active domains)
			foreach ($languages as $language) {
				$total = $this->TranslateDomains->TranslateStrings->TranslateTerms
					->find()
					->innerJoinWith('TranslateStrings.TranslateDomains', function ($q) use ($id) {
						return $q->where([
							'TranslateDomains.translate_project_id' => $id,
							'TranslateDomains.active' => true,
						]);
					})
					->where([
						'TranslateTerms.translate_locale_id' => $language->id,
						'TranslateTerms.content IS NOT' => null,
						'TranslateTerms.content !=' => '',
					])
					->count();

				$confirmed = $this->TranslateDomains->TranslateStrings->TranslateTerms
					->find()
					->innerJoinWith('TranslateStrings.TranslateDomains', function ($q) use ($id) {
						return $q->where([
							'TranslateDomains.translate_project_id' => $id,
							'TranslateDomains.active' => true,
						]);
					})
					->where([
						'TranslateTerms.translate_locale_id' => $language->id,
						'TranslateTerms.content IS NOT' => null,
						'TranslateTerms.content !=' => '',
						'TranslateTerms.confirmed' => true,
					])
					->count();

				if ($total > 0) {
					$confirmationStats[$language->locale] = [
						'total' => $total,
						'confirmed' => $confirmed,
						'unconfirmed' => $total - $confirmed,
						'percentage' => (int)(($confirmed / $total) * 100),
						'locale' => $language,
					];
				}
			}

			// Get recently imported strings (last 30 days)
			$recentImports = $this->TranslateDomains->TranslateStrings
				->find()
				->contain(['TranslateDomains'])
				->where([
					'TranslateDomains.translate_project_id' => $id,
					'TranslateStrings.last_import IS NOT' => null,
					'TranslateStrings.last_import >=' => new DateTime('-30 days'),
				])
				->orderBy(['TranslateStrings.last_import' => 'DESC'])
				->limit(10)
				->toArray();
		}

		$this->set(compact('coverage', 'languages', 'count', 'projectSwitchArray', 'localeStats', 'recentStrings', 'recentTerms', 'auditLogs', 'confirmationStats', 'recentImports', 'auditData'));
	}

	/**
	 * @return void
	 */
	public function bestPractice() {
	}

	/**
	 * Reset terms and strings
	 * - optional: domains and languages
	 *
	 * @return \Cake\Http\Response|null|void
	 */
	public function reset() {
		if ($this->Translation->isPosted()) {
			$selection = (array)$this->request->getData('Form.sel');
			if ($this->request->getQuery('hard-reset')) {
				$this->Translation->hardReset();
				$this->Flash->success('Hard reset done.');

				return $this->redirect(['action' => 'index']);
			}

			$projectId = $this->Translation->currentProjectId();
			if (!$projectId) {
				$this->Flash->error(__d('translate', 'No project selected.'));

				return $this->redirect(['action' => 'index']);
			}

			$types = [];
			$languages = [];

			foreach ($selection as $sel) {
				if (!empty($sel)) {
					switch ($sel) {
						case 'terms':
							$types[] = 'terms';

							break;
						case 'strings':
							$types[] = 'strings';

							break;
						case 'groups':
						case 'domains':
							$types[] = 'domains';

							break;
						case 'languages':
							$types[] = 'languages';

							break;
					}
				}
			}

			if ($types) {
				$this->TranslateDomains->TranslateProjects->reset($projectId, $types, $languages);
				$this->Flash->success(__d('translate', 'Reset complete for current project.'));
			} else {
				$this->Flash->warning(__d('translate', 'No options selected.'));
			}

			return $this->redirect(['action' => 'index']);
		}

		//FIXME
		//$this->request->data['Form']['sel'][] = 'terms';
		//$this->request->data['Form']['sel'][] = 'strings';

		//$this->request->data['Form']['sel']['languages'] = 0;
		//$this->request->data['Form']['sel']['groups'] = 0;
	}

	/**
	 * @return void
	 */
	public function translate() {
		$this->request->allowMethod(['post']);

		$text = $this->request->getData('text');
		$to = $this->request->getData('to');
		$from = $this->request->getData('from');

		$translator = new Translator();
		$translation = $translator->translate($text, $to, $from);

		$this->set(compact('translation'));
		$this->viewBuilder()->setOption('serialize', ['translation']);
	}

	/**
	 * Convert text for PO files.
	 *
	 * @return void
	 */
	public function convert() {
		if ($this->Translation->isPosted()) {
			$settings = (array)$this->request->getData();
			$text = $this->request->getData('input');

			$ConvertLib = new ConvertLib();
			$text = $ConvertLib->convert($text, $settings);
			$this->set(compact('text'));
		}
	}

	/**
	 * Switch the application language/locale
	 *
	 * @return \Cake\Http\Response|null
	 */
	public function switchLanguage() {
		$locale = $this->request->getQuery('locale');
		if (!$locale) {
			$this->Flash->error(__d('translate', 'Invalid locale'));

			return $this->redirect(['action' => 'index']);
		}

		// Validate locale exists, is active, and belongs to current project
		$language = $this->fetchTable('Translate.TranslateLocales')
			->find()
			->where([
				'locale' => $locale,
				'active' => true,
				'translate_project_id' => $this->Translation->currentProjectId(),
			])
			->first();

		if (!$language) {
			$this->Flash->error(__d('translate', 'Language not found or not active'));

			return $this->redirect(['action' => 'index']);
		}

		// Store locale in session or cookie
		$this->request->getSession()->write('Config.language', $locale);

		$this->Flash->success(__d('translate', 'Language switched to {0}', $language->name));

		// Redirect back to previous page or index
		$redirect = $this->request->getQuery('redirect') ?: ['action' => 'index'];

		return $this->redirect($redirect);
	}

	/**
	 * Switch the current project.
	 *
	 * @return \Cake\Http\Response|null
	 */
	public function switchProject() {
		$this->request->allowMethod(['post']);

		$projectId = (int)$this->request->getData('project_switch');
		if (!$projectId) {
			$this->Flash->error(__d('translate', 'Invalid project'));

			return $this->redirect(['action' => 'index']);
		}

		$translateProject = $this->TranslateDomains->TranslateProjects->get($projectId);

		$this->request->getSession()->write('TranslateProject.id', $translateProject->id);
		$this->Flash->success(__d('translate', 'Project switched to {0}', $translateProject->name));

		return $this->Translation->autoRedirect(['action' => 'index']);
	}

}
