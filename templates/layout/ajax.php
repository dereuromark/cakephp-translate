<?php
/**
 * @var \App\View\AppView $this
 */
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?= $this->fetch('meta') ?>
	<?= $this->fetch('css') ?>

	<style>
		body {
			font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
			font-size: 0.875rem;
			line-height: 1.5;
			color: #212529;
			background-color: #fff;
			padding: 1rem;
		}

		.code-excerpt {
			background-color: #f8f9fa;
			border: 1px solid #dee2e6;
			border-radius: 0.375rem;
			padding: 1rem;
			overflow-x: auto;
		}

		.code-excerpt pre {
			margin: 0;
			font-family: 'Courier New', Courier, monospace;
			font-size: 0.875rem;
			line-height: 1.6;
			white-space: pre;
		}

		.code-excerpt .highlight {
			background-color: #fff3cd;
			display: block;
			margin: 0 -0.5rem;
			padding: 0 0.5rem;
			border-left: 3px solid #ffc107;
		}

		a {
			color: #0d6efd;
			text-decoration: none;
		}

		a:hover {
			color: #0a58ca;
			text-decoration: underline;
		}
	</style>
</head>
<body>
	<?= $this->Flash->render() ?>
	<?= $this->fetch('content') ?>
	<?= $this->fetch('script') ?>
</body>
</html>
