<?php
/**
 * @var \App\View\AppView $this
 * @var string|null $title
 */

$title = $this->fetch('title');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title><?= $title ? h($title) . ' - ' : '' ?>Translation Manager</title>

	<!-- Bootstrap 5 CSS -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" integrity="sha512-jnSuA4Ss2PkkikSOLtYs8BlYIeeIK1h99ty4YfvRPAlzr377vr3CXDb7sb7eEEBYjDtcYj+AjBH3FLv5uSJuXg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

	<!-- Font Awesome 6 -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

	<!-- Flag Icons -->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.2.3/css/flag-icons.min.css" />

	<?= $this->fetch('meta') ?>
	<?= $this->fetch('css') ?>

	<style>
		:root {
			--primary-color: #0d6efd;
			--secondary-color: #6c757d;
			--success-color: #198754;
			--danger-color: #dc3545;
			--warning-color: #ffc107;
			--info-color: #0dcaf0;
			--light-bg: #f8f9fa;
			--dark-text: #212529;
			--border-color: #dee2e6;
		}

		body {
			font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
			font-size: 1rem;
			line-height: 1.5;
			color: var(--dark-text);
			background-color: #fff;
			min-height: 100vh;
			display: flex;
			flex-direction: column;
		}

		.navbar {
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			box-shadow: 0 2px 4px rgba(0,0,0,.1);
		}

		.navbar-brand {
			font-weight: 600;
			font-size: 1.25rem;
			color: #fff !important;
		}

		.navbar-brand i {
			margin-right: 0.5rem;
		}

		.main-content {
			flex: 1;
			padding: 2rem 0;
			background-color: var(--light-bg);
		}

		.content-wrapper {
			background-color: #fff;
			border-radius: 0.5rem;
			box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,.075);
			padding: 2rem;
			min-height: 400px;
		}

		.page-header {
			margin-bottom: 2rem;
			padding-bottom: 1rem;
			border-bottom: 2px solid var(--border-color);
		}

		.page-header h1 {
			font-size: 2rem;
			font-weight: 600;
			color: var(--dark-text);
			margin: 0;
		}

		.flash-messages {
			position: relative;
			z-index: 1050;
		}

		.alert {
			border-radius: 0.375rem;
			border: none;
			box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,.075);
			margin-bottom: 1rem;
		}

		.alert i {
			margin-right: 0.5rem;
		}

		.defaulting {
			color: #999;
			font-style: italic;
		}

		.highlight {
			background-color: #F2FF9F;
			padding: 0.125rem 0.25rem;
			border-radius: 0.25rem;
		}

		.btn,
		input[type="submit"].btn {
			border-radius: 0.375rem;
			font-weight: 500;
			padding: 0.5rem 1rem;
			transition: all 0.2s ease-in-out;
			border: 1px solid transparent;
			display: inline-block;
			text-align: center;
			vertical-align: middle;
			cursor: pointer;
			user-select: none;
			line-height: 1.5;
		}

		.btn i {
			margin-right: 0.375rem;
		}

		.btn:hover,
		input[type="submit"].btn:hover {
			transform: translateY(-1px);
			box-shadow: 0 0.25rem 0.5rem rgba(0,0,0,.15);
		}

		input[type="submit"].btn:active {
			transform: translateY(0);
		}

		.table {
			border-radius: 0.375rem;
			overflow: hidden;
		}

		.table thead th {
			background-color: var(--light-bg);
			border-bottom: 2px solid var(--border-color);
			font-weight: 600;
			text-transform: uppercase;
			font-size: 0.875rem;
			letter-spacing: 0.5px;
		}

		.card {
			border: none;
			border-radius: 0.5rem;
			box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,.075);
			margin-bottom: 1.5rem;
		}

		.card-header {
			background-color: var(--light-bg);
			border-bottom: 1px solid var(--border-color);
			font-weight: 600;
			padding: 1rem 1.25rem;
		}

		footer {
			background-color: #343a40;
			color: #fff;
			padding: 1.5rem 0;
			margin-top: auto;
			border-top: 3px solid #667eea;
		}

		footer a {
			color: #adb5bd;
			text-decoration: none;
			transition: color 0.2s;
		}

		footer a:hover {
			color: #fff;
		}

		.loading {
			opacity: 0.6;
			pointer-events: none;
		}

		.spinner-border-sm {
			width: 1rem;
			height: 1rem;
			border-width: 0.15em;
		}

		@media (max-width: 768px) {
			.content-wrapper {
				padding: 1rem;
			}

			.page-header h1 {
				font-size: 1.5rem;
			}

			.main-content {
				padding: 1rem 0;
			}
		}

		/* Custom scrollbar */
		::-webkit-scrollbar {
			width: 10px;
		}

		::-webkit-scrollbar-track {
			background: var(--light-bg);
		}

		::-webkit-scrollbar-thumb {
			background: var(--secondary-color);
			border-radius: 5px;
		}

		::-webkit-scrollbar-thumb:hover {
			background: #555;
		}
	</style>
</head>
<body>

	<!-- Navbar -->
	<nav class="navbar navbar-expand-lg navbar-dark">
		<div class="container-fluid">
			<a class="navbar-brand" href="<?= $this->Url->build(['controller' => 'Translate', 'action' => 'index']) ?>">
				<i class="fas fa-language"></i>
				Translation Manager
			</a>
			<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>
			<div class="collapse navbar-collapse" id="navbarContent">
				<div class="ms-auto">
					<?= $this->element('Translate.project_switch') ?>
				</div>
			</div>
		</div>
	</nav>

	<!-- Main Content -->
	<main class="main-content">
		<div class="container-fluid">
			<!-- Flash Messages -->
			<div class="flash-messages">
				<?= $this->Flash->render() ?>
			</div>

			<!-- Content -->
			<div class="content-wrapper">
				<?= $this->fetch('content') ?>
			</div>
		</div>
	</main>

	<!-- Footer -->
	<footer class="text-center">
		<div class="container">
			<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
				<div class="flex-grow-1">
					<?= $this->element('Translate.footer_copyright') ?>
				</div>
				<div class="flex-shrink-0">
					<?= $this->element('Translate.language_switcher') ?>
				</div>
			</div>
		</div>
	</footer>

	<!-- jQuery -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

	<!-- Bootstrap Bundle (includes Popper) -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js" integrity="sha512-7Pi/otdlbbCR+LnW+F7PwFcSDJOuUJB3OxtEHbg4vSMvzvJjde4Po1v4BR9Gdc9aXNUNFVUY+SK51wWT8WF0Gg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

	<script>
		$(document).ready(function() {
			// Add loading state to buttons on form submit
			$('form').on('submit', function() {
				var $btn = $(this).find('button[type="submit"]');
				$btn.prop('disabled', true);
				if ($btn.find('.spinner-border').length === 0) {
					$btn.prepend('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>');
				}
			});

			// Confirm delete actions
			$('.btn-danger, a[href*="delete"]').on('click', function(e) {
				if ($(this).data('confirm') !== false) {
					if (!confirm('Are you sure you want to delete this item?')) {
						e.preventDefault();
						return false;
					}
				}
			});

			// Initialize Bootstrap tooltips
			var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
			tooltipTriggerList.map(function (tooltipTriggerEl) {
				return new bootstrap.Tooltip(tooltipTriggerEl);
			});

			// Initialize Bootstrap popovers
			var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
			popoverTriggerList.map(function (popoverTriggerEl) {
				return new bootstrap.Popover(popoverTriggerEl);
			});
		});
	</script>

	<?= $this->fetch('script') ?>

</body>
</html>
