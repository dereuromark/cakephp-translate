<?php
/**
 * @var \App\View\AppView $this
 * @var array<\Translate\Model\Entity\TranslateLocale> $locales
 * @var array<\Translate\Model\Entity\TranslateDomain> $domains
 * @var array $stats
 * @var array $localeTotals
 * @var array $domainTotals
 * @var array $grandTotal
 */
?>

<div class="row">
	<aside class="col-md-3 col-sm-4 col-12">
		<div class="card">
			<div class="card-header">
				<h3 class="card-title"><i class="fa-solid fa-bars"></i> <?= __d('translate', 'Actions') ?></h3>
			</div>
			<div class="list-group list-group-flush">
				<?= $this->Html->link(__d('translate', 'Overview'), ['action' => 'index'], ['class' => 'list-group-item list-group-item-action']) ?>
				<?= $this->Html->link(__d('translate', 'List Strings'), ['controller' => 'TranslateStrings', 'action' => 'index'], ['class' => 'list-group-item list-group-item-action']) ?>
				<?= $this->Html->link(__d('translate', 'Start Translating'), ['controller' => 'TranslateStrings', 'action' => 'translate'], ['class' => 'list-group-item list-group-item-action']) ?>
			</div>
		</div>

		<!-- Grand Total Summary -->
		<div class="card mt-3">
			<div class="card-header bg-primary text-white">
				<h3 class="card-title mb-0"><i class="fa-solid fa-chart-pie"></i> <?= __d('translate', 'Grand Total') ?></h3>
			</div>
			<div class="card-body">
				<div class="mb-3">
					<strong><?= __d('translate', 'Translation Progress') ?></strong>
					<div class="progress mt-1" style="height: 25px;">
						<div class="progress-bar <?= $grandTotal['translation_percentage'] >= 80 ? 'bg-success' : ($grandTotal['translation_percentage'] >= 50 ? 'bg-warning' : 'bg-danger') ?>"
							role="progressbar"
							style="width: <?= $grandTotal['translation_percentage'] ?>%"
							aria-valuenow="<?= $grandTotal['translation_percentage'] ?>"
							aria-valuemin="0"
							aria-valuemax="100">
							<strong><?= $grandTotal['translation_percentage'] ?>%</strong>
						</div>
					</div>
					<small class="text-muted">
						<?= number_format($grandTotal['translated']) ?> / <?= number_format($grandTotal['total_strings']) ?> <?= __d('translate', 'strings') ?>
					</small>
				</div>

				<div>
					<strong><?= __d('translate', 'Confirmation Progress') ?></strong>
					<div class="progress mt-1" style="height: 25px;">
						<div class="progress-bar bg-info"
							role="progressbar"
							style="width: <?= $grandTotal['confirmation_percentage'] ?>%"
							aria-valuenow="<?= $grandTotal['confirmation_percentage'] ?>"
							aria-valuemin="0"
							aria-valuemax="100">
							<strong><?= $grandTotal['confirmation_percentage'] ?>%</strong>
						</div>
					</div>
					<small class="text-muted">
						<?= number_format($grandTotal['confirmed']) ?> / <?= number_format($grandTotal['translated']) ?> <?= __d('translate', 'confirmed') ?>
					</small>
				</div>
			</div>
		</div>
	</aside>

	<div class="col-md-9 col-sm-8 col-12">
		<!-- Locale Summary -->
		<div class="card mb-3">
			<div class="card-header">
				<h3 class="card-title"><i class="fa-solid fa-globe"></i> <?= __d('translate', 'Progress by Locale') ?></h3>
			</div>
			<div class="card-body p-0">
				<div class="table-responsive">
					<table class="table table-striped table-hover mb-0">
						<thead class="table-dark">
							<tr>
								<th><?= __d('translate', 'Locale') ?></th>
								<th class="text-center"><?= __d('translate', 'Total') ?></th>
								<th class="text-center"><?= __d('translate', 'Translated') ?></th>
								<th class="text-center"><?= __d('translate', 'Untranslated') ?></th>
								<th><?= __d('translate', 'Translation') ?></th>
								<th class="text-center"><?= __d('translate', 'Confirmed') ?></th>
								<th class="text-center"><?= __d('translate', 'Unconfirmed') ?></th>
								<th><?= __d('translate', 'Confirmation') ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($localeTotals as $data) { ?>
							<tr>
								<td>
									<?= $this->Translation->flag($this->Translation->resolveFlagCode($data['locale'])) ?>
									<strong><?= h($data['locale']->name) ?></strong>
									<small class="text-muted">(<?= h($data['locale']->locale) ?>)</small>
								</td>
								<td class="text-center"><?= number_format($data['total_strings']) ?></td>
								<td class="text-center">
									<span class="badge bg-success"><?= number_format($data['translated']) ?></span>
								</td>
								<td class="text-center">
									<?php if ($data['untranslated'] > 0) { ?>
										<span class="badge bg-danger"><?= number_format($data['untranslated']) ?></span>
									<?php } else { ?>
										<span class="badge bg-secondary">0</span>
									<?php } ?>
								</td>
								<td style="min-width: 150px;">
									<div class="progress" style="height: 20px;">
										<div class="progress-bar <?= $data['translation_percentage'] >= 80 ? 'bg-success' : ($data['translation_percentage'] >= 50 ? 'bg-warning' : 'bg-danger') ?>"
											role="progressbar"
											style="width: <?= $data['translation_percentage'] ?>%"
											aria-valuenow="<?= $data['translation_percentage'] ?>"
											aria-valuemin="0"
											aria-valuemax="100">
											<small><strong><?= $data['translation_percentage'] ?>%</strong></small>
										</div>
									</div>
								</td>
								<td class="text-center">
									<span class="badge bg-info"><?= number_format($data['confirmed']) ?></span>
								</td>
								<td class="text-center">
									<?php if ($data['unconfirmed'] > 0) { ?>
										<span class="badge bg-warning text-dark"><?= number_format($data['unconfirmed']) ?></span>
									<?php } else { ?>
										<span class="badge bg-secondary">0</span>
									<?php } ?>
								</td>
								<td style="min-width: 150px;">
									<div class="progress" style="height: 20px;">
										<div class="progress-bar bg-info"
											role="progressbar"
											style="width: <?= $data['confirmation_percentage'] ?>%"
											aria-valuenow="<?= $data['confirmation_percentage'] ?>"
											aria-valuemin="0"
											aria-valuemax="100">
											<small><strong><?= $data['confirmation_percentage'] ?>%</strong></small>
										</div>
									</div>
								</td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>

		<!-- Domain Breakdown -->
		<?php foreach ($domains as $domain) { ?>
		<div class="card mb-3">
			<div class="card-header">
				<h3 class="card-title">
					<i class="fa-solid fa-folder"></i>
					<?= h($domain->name) ?>
					<small class="text-muted">(<?= number_format($domainTotals[$domain->id]['total_strings']) ?> <?= __d('translate', 'strings') ?>)</small>
				</h3>
			</div>
			<div class="card-body p-0">
				<div class="table-responsive">
					<table class="table table-sm table-hover mb-0">
						<thead>
							<tr>
								<th><?= __d('translate', 'Locale') ?></th>
								<th class="text-center"><?= __d('translate', 'Translated') ?></th>
								<th class="text-center"><?= __d('translate', 'Untranslated') ?></th>
								<th><?= __d('translate', 'Translation') ?></th>
								<th class="text-center"><?= __d('translate', 'Confirmed') ?></th>
								<th class="text-center"><?= __d('translate', 'Unconfirmed') ?></th>
								<th><?= __d('translate', 'Confirmation') ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($locales as $locale) {
								$stat = $stats[$domain->id][$locale->id];
							?>
							<tr>
								<td>
									<?= $this->Translation->flag($this->Translation->resolveFlagCode($locale)) ?>
									<?= h($locale->locale) ?>
								</td>
								<td class="text-center">
									<span class="badge bg-success"><?= number_format($stat['translated']) ?></span>
								</td>
								<td class="text-center">
									<?php if ($stat['untranslated'] > 0) { ?>
										<span class="badge bg-danger"><?= number_format($stat['untranslated']) ?></span>
									<?php } else { ?>
										<span class="badge bg-secondary">0</span>
									<?php } ?>
								</td>
								<td style="min-width: 120px;">
									<div class="progress" style="height: 18px;">
										<div class="progress-bar <?= $stat['translation_percentage'] >= 80 ? 'bg-success' : ($stat['translation_percentage'] >= 50 ? 'bg-warning' : 'bg-danger') ?>"
											role="progressbar"
											style="width: <?= $stat['translation_percentage'] ?>%"
											aria-valuenow="<?= $stat['translation_percentage'] ?>"
											aria-valuemin="0"
											aria-valuemax="100">
											<small><?= $stat['translation_percentage'] ?>%</small>
										</div>
									</div>
								</td>
								<td class="text-center">
									<span class="badge bg-info"><?= number_format($stat['confirmed']) ?></span>
								</td>
								<td class="text-center">
									<?php if ($stat['unconfirmed'] > 0) { ?>
										<span class="badge bg-warning text-dark"><?= number_format($stat['unconfirmed']) ?></span>
									<?php } else { ?>
										<span class="badge bg-secondary">0</span>
									<?php } ?>
								</td>
								<td style="min-width: 120px;">
									<div class="progress" style="height: 18px;">
										<div class="progress-bar bg-info"
											role="progressbar"
											style="width: <?= $stat['confirmation_percentage'] ?>%"
											aria-valuenow="<?= $stat['confirmation_percentage'] ?>"
											aria-valuemin="0"
											aria-valuemax="100">
											<small><?= $stat['confirmation_percentage'] ?>%</small>
										</div>
									</div>
								</td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<?php } ?>

		<?php if (empty($domains)) { ?>
		<div class="alert alert-info">
			<i class="fa-solid fa-info-circle"></i>
			<?= __d('translate', 'No active domains found. Please activate at least one domain to see statistics.') ?>
		</div>
		<?php } ?>

		<?php if (empty($locales)) { ?>
		<div class="alert alert-warning">
			<i class="fa-solid fa-exclamation-triangle"></i>
			<?= __d('translate', 'No active locales found. Please add at least one locale to see statistics.') ?>
		</div>
		<?php } ?>
	</div>
</div>
