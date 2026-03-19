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
				'translate_project_id IS' => $id,
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
						'TranslateDomains.translate_project_id IS' => $id,
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
				->where(['TranslateDomains.translate_project_id IS' => $id])
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
				->where(['TranslateDomains.translate_project_id IS' => $id])
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
							'TranslateDomains.translate_project_id IS' => $id,
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
							'TranslateDomains.translate_project_id IS' => $id,
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
					'TranslateDomains.translate_project_id IS' => $id,
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

	/**
	 * Detailed translation progress statistics page.
	 *
	 * Shows translation completeness and confirmation status
	 * broken down by locale and domain.
	 *
	 * @return \Cake\Http\Response|null|void
	 */
	public function stats() {
		$projectId = $this->Translation->currentProjectId();
		if (!$projectId) {
			$this->Flash->error(__d('translate', 'No project selected.'));

			return $this->redirect(['action' => 'index']);
		}

		$translateLocalesTable = $this->fetchTable('Translate.TranslateLocales');
		$translateStringsTable = $this->fetchTable('Translate.TranslateStrings');
		$translateTermsTable = $this->fetchTable('Translate.TranslateTerms');

		// Get active locales for the project
		/** @var array<\Translate\Model\Entity\TranslateLocale> $locales */
		$locales = $translateLocalesTable->find()
			->where([
				'translate_project_id' => $projectId,
				'active' => true,
			])
			->orderBy(['name' => 'ASC'])
			->toArray();

		// Get active domains for the project
		/** @var array<\Translate\Model\Entity\TranslateDomain> $domains */
		$domains = $this->TranslateDomains->find()
			->where([
				'translate_project_id' => $projectId,
				'active' => true,
			])
			->orderBy(['name' => 'ASC'])
			->toArray();

		// Calculate stats per locale and domain
		$stats = [];
		$localeTotals = [];
		$domainTotals = [];

		foreach ($locales as $locale) {
			$localeTotals[$locale->id] = [
				'locale' => $locale,
				'total_strings' => 0,
				'translated' => 0,
				'confirmed' => 0,
				'untranslated' => 0,
				'unconfirmed' => 0,
				'translation_percentage' => 0,
				'confirmation_percentage' => 0,
			];
		}

		foreach ($domains as $domain) {
			// Count total strings in this domain
			$totalStrings = $translateStringsTable->find()
				->where(['translate_domain_id' => $domain->id])
				->count();

			$domainTotals[$domain->id] = [
				'domain' => $domain,
				'total_strings' => $totalStrings,
			];

			foreach ($locales as $locale) {
				$localeId = $locale->id;

				// Count translated strings for this locale/domain
				$translated = $translateTermsTable->find()
					->innerJoinWith('TranslateStrings', function ($q) use ($domain) {
						return $q->where(['TranslateStrings.translate_domain_id' => $domain->id]);
					})
					->where([
						'TranslateTerms.translate_locale_id' => $localeId,
						'TranslateTerms.content IS NOT' => null,
						'TranslateTerms.content !=' => '',
					])
					->count();

				// Count confirmed translations for this locale/domain
				$confirmed = $translateTermsTable->find()
					->innerJoinWith('TranslateStrings', function ($q) use ($domain) {
						return $q->where(['TranslateStrings.translate_domain_id' => $domain->id]);
					})
					->where([
						'TranslateTerms.translate_locale_id' => $localeId,
						'TranslateTerms.content IS NOT' => null,
						'TranslateTerms.content !=' => '',
						'TranslateTerms.confirmed' => true,
					])
					->count();

				$stats[$domain->id][$localeId] = [
					'total' => $totalStrings,
					'translated' => $translated,
					'confirmed' => $confirmed,
					'untranslated' => $totalStrings - $translated,
					'unconfirmed' => $translated - $confirmed,
					'translation_percentage' => $totalStrings > 0 ? (int)(($translated / $totalStrings) * 100) : 0,
					'confirmation_percentage' => $translated > 0 ? (int)(($confirmed / $translated) * 100) : 0,
				];

				// Accumulate locale totals
				if (isset($localeTotals[$localeId])) {
					$localeTotals[$localeId]['total_strings'] += $totalStrings;
					$localeTotals[$localeId]['translated'] += $translated;
					$localeTotals[$localeId]['confirmed'] += $confirmed;
				}
			}
		}

		// Calculate locale total percentages
		foreach ($localeTotals as $localeId => $data) {
			$localeTotals[$localeId]['untranslated'] = $data['total_strings'] - $data['translated'];
			$localeTotals[$localeId]['unconfirmed'] = $data['translated'] - $data['confirmed'];
			$localeTotals[$localeId]['translation_percentage'] = $data['total_strings'] > 0
				? (int)(($data['translated'] / $data['total_strings']) * 100)
				: 0;
			$localeTotals[$localeId]['confirmation_percentage'] = $data['translated'] > 0
				? (int)(($data['confirmed'] / $data['translated']) * 100)
				: 0;
		}

		// Calculate grand totals
		$grandTotal = [
			'total_strings' => 0,
			'translated' => 0,
			'confirmed' => 0,
		];
		foreach ($localeTotals as $data) {
			$grandTotal['total_strings'] += $data['total_strings'];
			$grandTotal['translated'] += $data['translated'];
			$grandTotal['confirmed'] += $data['confirmed'];
		}
		$grandTotal['untranslated'] = $grandTotal['total_strings'] - $grandTotal['translated'];
		$grandTotal['unconfirmed'] = $grandTotal['translated'] - $grandTotal['confirmed'];
		$grandTotal['translation_percentage'] = $grandTotal['total_strings'] > 0
			? (int)(($grandTotal['translated'] / $grandTotal['total_strings']) * 100)
			: 0;
		$grandTotal['confirmation_percentage'] = $grandTotal['translated'] > 0
			? (int)(($grandTotal['confirmed'] / $grandTotal['translated']) * 100)
			: 0;

		$this->set(compact('locales', 'domains', 'stats', 'localeTotals', 'domainTotals', 'grandTotal'));
	}

}
