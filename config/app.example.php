<?php

use Cake\Http\ServerRequest;
use Translate\Translator\Engine\Google;

return [
	'Translate' => [
		'noComments' => true, // Do not output comments/references into PO files
		'plurals' => 2,
		'languagesTable' => null,
		'usersTable' => null,
		'defaultLocale' => null, // E.g. `en` or `en_US`
		'layout' => null, // Defaults to own layout
		'editor' => false, // Code editor on references,
		'showCodeReferences' => false, // Enable to show also in frontend the code snippets where the translation is used

		'engine' => Google::class, // Translation API engine class (or array of fallback engine classes)
		'flags' => null, // Flag image format for the language switcher; set to 'gif' to use .gif flag icons
		'iconMap' => [], // Optional icon-name map merged into Icon.map for the admin UI
		'pluralExpression' => 'n != 1', // Gettext Plural-Forms expression written into dumped PO files
		'shadowTableSuffix' => '_translations', // Suffix for generated translation shadow tables (e.g. '_i18n')
		'locales' => null, // Array of supported locale codes for the admin UI (e.g. ['en_US', 'de_DE']); null = auto-detect
		'disableAuditLog' => false, // Set true to skip AuditStash logging on translate tables (only relevant when AuditStash is installed)

		// Admin access gate. REQUIRED for the Admin-prefixed backend — the host
		// app MUST set this to a Closure that returns literal true to grant
		// access; anything else (unset, non-Closure, returns false, returns a
		// truthy non-bool, or throws) yields a 403. The admin UI writes to disk,
		// mutates the i18n catalog and generates migrations, so the default
		// policy is deny.
		'adminAccess' => function (ServerRequest $request): bool {
			$identity = $request->getAttribute('identity');

			return $identity !== null && in_array('admin', (array)$identity->roles, true);
		},

		// Back-to-App link in the admin header (opt-in). When set, an outline
		// button appears in the top navbar so admins can escape the
		// plugin-isolated layout. Accepts anything Router::url() takes — Cake
		// URL array, path string, or full URL. Use 'plugin' => false to
		// anchor the builder to the host app rather than the Translate plugin.
		// 'adminBackUrl' => ['plugin' => false, 'prefix' => 'Admin', 'controller' => 'Overview', 'action' => 'index'],
		// 'adminBackLabel' => 'Back to admin', // Optional. Defaults to "Back to App".
	],
];
