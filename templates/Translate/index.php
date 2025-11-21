<?php
/**
 * @var \App\View\AppView $this
 * @var array $count
 * @var mixed $coverage
 * @var array $projectSwitchArray
 * @var array $domainStats
 */

$totalCoverage = (int)$this->Translation->totalCoverage($coverage);
$totalColor = $this->Translation->getColor($totalCoverage);
?>

<div class="translate-index">
	<div class="page-header mb-4">
		<h1>
			<i class="fas fa-language"></i>
			<?= __d('translate', 'Translate Plugin'); ?>
		</h1>
		<p class="lead text-muted">
			<?= __d('translate', 'Easily manage i18n/translations from your backend.') ?>
		</p>
	</div>

	<div class="row g-4">
		<!-- Status Card -->
		<div class="col-lg-6">
			<div class="card h-100">
				<div class="card-header">
					<h3 class="mb-0">
						<i class="fas fa-chart-pie"></i>
						<?= __d('translate', 'Status') ?>
					</h3>
				</div>
				<div class="card-body">
					<div class="alert alert-info mb-4">
						<div class="d-flex align-items-center">
							<div class="flex-shrink-0">
								<i class="fas fa-globe fa-3x"></i>
							</div>
							<div class="flex-grow-1 ms-3">
								<h5 class="mb-1"><?= __d('translate', 'Current Translation Coverage') ?></h5>
								<p class="mb-0">
									<span style="color:#<?= $totalColor; ?>;font-weight:bold;font-size:2rem;">
										<?= $totalCoverage ?>%
									</span>
									<span class="text-muted"><?= __d('translate', 'translated') ?></span>
								</p>
							</div>
						</div>
					</div>

					<?php if (!empty($coverage) && is_array($count)) { ?>
						<?= $this->element('coverage_table', []) ?>

						<div class="row text-center mt-4">
							<div class="col-3">
								<?= $this->Html->link(
									'<div class="card border-success">
										<div class="card-body">
											<i class="fas fa-flag text-success fa-2x mb-2"></i>
											<h4 class="mb-0">' . $count['locales'] . '</h4>
											<small class="text-muted">' . h(__d('translate', 'Locales')) . '</small>
										</div>
									</div>',
									['action' => 'terms'],
									['escape' => false, 'class' => 'text-decoration-none'],
								) ?>
							</div>
							<div class="col-3">
								<?= $this->Html->link(
									'<div class="card border-primary">
										<div class="card-body">
											<i class="fas fa-folder text-primary fa-2x mb-2"></i>
											<h4 class="mb-0">' . $count['domains'] . '</h4>
											<small class="text-muted">' . h(__d('translate', 'Domains')) . '</small>
										</div>
									</div>',
									['action' => 'terms'],
									['escape' => false, 'class' => 'text-decoration-none'],
								) ?>
							</div>
							<div class="col-3">
								<?= $this->Html->link(
									'<div class="card border-info">
										<div class="card-body">
											<i class="fas fa-file-alt text-info fa-2x mb-2"></i>
											<h4 class="mb-0">' . $count['strings'] . '</h4>
											<small class="text-muted">' . h(__d('translate', 'Strings')) . '</small>
										</div>
									</div>',
									['action' => 'terms'],
									['escape' => false, 'class' => 'text-decoration-none'],
								) ?>
							</div>
							<div class="col-3">
								<?= $this->Html->link(
									'<div class="card border-warning">
										<div class="card-body">
											<i class="fas fa-language text-warning fa-2x mb-2"></i>
											<h4 class="mb-0">' . $count['translations'] . '</h4>
											<small class="text-muted">' . h(__d('translate', 'Translations')) . '</small>
										</div>
									</div>',
									['action' => 'terms'],
									['escape' => false, 'class' => 'text-decoration-none'],
								) ?>
							</div>
						</div>
					<?php } ?>
				</div>
			</div>
		</div>

		<!-- Quick Start Card -->
		<div class="col-lg-6">
			<div class="card h-100">
				<div class="card-header">
					<h3 class="mb-0">
						<i class="fas fa-rocket"></i>
						<?= __d('translate', 'Quick Start') ?>
					</h3>
				</div>
				<div class="card-body">
					<?php if (!empty($domainStats)) { ?>
						<h5 class="mb-3">
							<i class="fas fa-folder-open text-primary"></i>
							<?= __d('translate', 'Select Domain') ?>
						</h5>
						<div class="list-group mb-4">
							<?php foreach ($domainStats as $domainName => $data) { ?>
								<?php
								$percentage = $data['percentage'];
								$progressColor = $percentage >= 80 ? 'success' : ($percentage >= 50 ? 'warning' : 'danger');
								?>
								<?= $this->Html->link(
									'<div class="d-flex w-100 justify-content-between align-items-center">
										<div>
											<strong>' . h($domainName) . '</strong>
											<br>
											<small class="text-muted">' . $data['translated'] . ' / ' . $data['total'] . ' ' . __d('translate', 'translated') . '</small>
										</div>
										<div class="text-end">
											<div class="progress" style="width: 100px; height: 20px;">
												<div class="progress-bar bg-' . $progressColor . '" role="progressbar" style="width: ' . $percentage . '%">
													' . $percentage . '%
												</div>
											</div>
										</div>
									</div>',
									['action' => 'terms', '?' => ['domain' => $domainName]],
									['class' => 'list-group-item list-group-item-action', 'escape' => false],
								) ?>
							<?php } ?>
						</div>
					<?php } ?>

					<div class="d-grid gap-2">
						<?= $this->Html->link(
							'<i class="fas fa-play"></i> ' . __d('translate', 'Start Translating Next'),
							['action' => 'translate'],
							['class' => 'btn btn-success btn-lg', 'escape' => false],
						); ?>
						<?= $this->Html->link(
							'<i class="fas fa-list"></i> ' . __d('translate', 'Browse All Terms'),
							['action' => 'terms'],
							['class' => 'btn btn-primary', 'escape' => false],
						); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
