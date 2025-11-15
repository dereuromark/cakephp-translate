<?php
/**
 * @var \App\View\AppView $this
 */

use Cake\Core\Configure;

$projectId = $this->request->getSession()->read('TranslateProject.id');
if (!$projectId) {
	return;
}

$TranslateLocales = \Cake\ORM\TableRegistry::getTableLocator()->get('Translate.TranslateLocales');
$activeLanguages = $TranslateLocales->find()
	->where([
		'active' => true,
		'translate_project_id' => $projectId,
	])
	->orderBy(['name' => 'ASC'])
	->all()
	->toArray();

if (!$activeLanguages) {
	return;
}

$currentLocale = $this->request->getSession()->read('Config.language') ?: Configure::read('App.defaultLocale') ?: 'en_US';
?>

<div class="language-switcher d-flex align-items-center gap-2 flex-wrap">
	<span class="text-muted"><i class="fas fa-globe"></i></span>
	<?php foreach ($activeLanguages as $language): ?>
		<?php
		// Use same flag resolution logic as TranslationHelper::resolveFlagCode()
		$flagCode = null;
		if ($language->language && $language->language->code) {
			$flagCode = $language->language->code;
		} elseif ($language->locale && str_contains($language->locale, '_')) {
			// Extract country code from locale (e.g., en_US -> us, de_DE -> de)
			[, $flagCode] = explode('_', $language->locale, 2);
		} elseif ($language->iso2) {
			$flagCode = $language->iso2;
		}

		$flagCode = strtolower($flagCode);
		$isActive = $language->locale === $currentLocale;
		?>
		<a
			href="<?= $this->Url->build(['prefix' => 'Admin', 'plugin' => 'Translate', 'controller' => 'Translate', 'action' => 'switchLanguage', '?' => ['locale' => $language->locale, 'redirect' => $this->request->getRequestTarget()]]) ?>"
			class="language-link <?= $isActive ? 'active' : '' ?>"
			title="<?= h($language->locale) ?>"
			data-bs-toggle="tooltip"
		>
			<span class="fi fi-<?= h($flagCode) ?>"></span>
			<span class="language-code"><?= h($language->iso2) ?></span>
		</a>
	<?php endforeach; ?>
</div>

<style>
	.language-switcher {
		font-size: 0.875rem;
	}

	.language-link {
		display: inline-flex;
		align-items: center;
		gap: 0.25rem;
		padding: 0.25rem 0.5rem;
		border-radius: 0.25rem;
		text-decoration: none;
		color: #adb5bd;
		transition: all 0.2s;
		border: 1px solid transparent;
	}

	.language-link:hover {
		color: #fff;
		background-color: rgba(255, 255, 255, 0.1);
		transform: translateY(-1px);
	}

	.language-link.active {
		color: #fff;
		border-color: rgba(255, 255, 255, 0.3);
		background-color: rgba(255, 255, 255, 0.15);
		font-weight: 600;
	}

	.language-link .fi {
		font-size: 1.1rem;
		line-height: 1;
	}

	.language-code {
		text-transform: uppercase;
		font-size: 0.75rem;
		font-weight: 500;
	}
</style>
