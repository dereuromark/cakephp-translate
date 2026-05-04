<?php

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

		// Back-to-App link in the admin header (opt-in). When set, an outline
		// button appears in the top navbar so admins can escape the
		// plugin-isolated layout. Accepts anything Router::url() takes — Cake
		// URL array, path string, or full URL. Use 'plugin' => false to
		// anchor the builder to the host app rather than the Translate plugin.
		// 'adminBackUrl' => ['plugin' => false, 'prefix' => 'Admin', 'controller' => 'Overview', 'action' => 'index'],
		// 'adminBackLabel' => 'Back to admin', // Optional. Defaults to "Back to App".
	],
];
