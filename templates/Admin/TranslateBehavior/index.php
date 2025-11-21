<?php
/**
 * @var \App\View\AppView $this
 * @var array $shadowTables
 * @var array $orphanedShadowTables
 * @var array $modelsWithBehavior
 * @var array $candidateTables
 * @var array $translationStrategies
 */
?>

<div class="row mb-3">
	<div class="col-12">
		<div class="card">
			<div class="card-header bg-primary text-white">
				<h5 class="mb-0">
					<i class="fas fa-language"></i> <?= __d('translate', 'CakePHP TranslateBehavior Overview') ?>
				</h5>
			</div>
			<div class="card-body">
				<p class="mb-0">
					<?= __d('translate', 'This page shows where CakePHP\'s built-in TranslateBehavior is used in your application and helps you add translation support to more tables.') ?>
				</p>
			</div>
		</div>
	</div>
</div>

<!-- Statistics Dashboard -->
<div class="row mb-3">
	<div class="col-md-3">
		<div class="card border-primary">
			<div class="card-body text-center">
				<i class="fas fa-table fa-2x text-primary mb-2"></i>
				<h3 class="mb-0"><?= count($shadowTables) ?></h3>
				<small class="text-muted"><?= __d('translate', 'Shadow Tables (_i18n)') ?></small>
			</div>
		</div>
	</div>
	<div class="col-md-3">
		<div class="card border-success">
			<div class="card-body text-center">
				<i class="fas fa-code fa-2x text-success mb-2"></i>
				<h3 class="mb-0"><?= count($modelsWithBehavior) ?></h3>
				<small class="text-muted"><?= __d('translate', 'Models Using Behavior') ?></small>
			</div>
		</div>
	</div>
	<div class="col-md-3">
		<div class="card border-info">
			<div class="card-body text-center">
				<i class="fas fa-plus-circle fa-2x text-info mb-2"></i>
				<h3 class="mb-0"><?= count($candidateTables) ?></h3>
				<small class="text-muted"><?= __d('translate', 'Candidate Tables') ?></small>
			</div>
		</div>
	</div>
	<div class="col-md-3">
		<div class="card border-warning">
			<div class="card-body text-center">
				<i class="fas fa-exclamation-triangle fa-2x text-warning mb-2"></i>
				<h3 class="mb-0"><?= count($orphanedShadowTables) ?></h3>
				<small class="text-muted"><?= __d('translate', 'Orphaned Shadow Tables') ?></small>
			</div>
		</div>
	</div>
</div>

<!-- Models Using TranslateBehavior -->
<?php if (!empty($modelsWithBehavior)) { ?>
<div class="row mb-3">
	<div class="col-12">
		<div class="card">
			<div class="card-header bg-success text-white">
				<h6 class="mb-0"><i class="fas fa-check-circle"></i> <?= __d('translate', 'Models Using TranslateBehavior') ?></h6>
			</div>
			<div class="card-body p-0">
				<div class="table-responsive">
					<table class="table table-hover table-sm mb-0">
						<thead class="table-light">
							<tr>
								<th><?= __d('translate', 'Table') ?></th>
								<th><?= __d('translate', 'Model Class') ?></th>
								<th><?= __d('translate', 'Strategy') ?></th>
								<th><?= __d('translate', 'Translated Fields') ?></th>
								<th><?= __d('translate', 'Shadow Table') ?></th>
								<th><?= __d('translate', 'Actions') ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($modelsWithBehavior as $info) { ?>
							<tr>
								<td><strong><?= h($info['table']) ?></strong></td>
								<td><small><code><?= h($info['model']) ?></code></small></td>
								<td>
									<span class="badge bg-<?= $info['strategy'] === 'eav' ? 'primary' : 'secondary' ?>">
										<?= h(strtoupper($info['strategy'])) ?>
									</span>
								</td>
								<td>
									<?php if (!empty($info['fields'])) { ?>
										<?php foreach ($info['fields'] as $field) { ?>
											<span class="badge bg-info"><?= h($field) ?></span>
										<?php } ?>
									<?php } else { ?>
										<span class="text-muted"><?= __d('translate', 'All fields') ?></span>
									<?php } ?>
								</td>
								<td>
									<?php if ($info['has_shadow_table']) { ?>
										<span class="badge bg-success">
											<i class="fas fa-check"></i> <?= h($info['table']) ?>_i18n
										</span>
									<?php } else { ?>
										<span class="badge bg-danger">
											<i class="fas fa-times"></i> <?= __d('translate', 'Missing') ?>
										</span>
									<?php } ?>
								</td>
								<td>
									<?php if ($info['has_shadow_table']) { ?>
										<?= $this->Html->link(
											'<i class="fas fa-eye"></i>',
											['action' => 'view', $info['table'] . '_i18n'],
											['escape' => false, 'class' => 'btn btn-sm btn-outline-primary', 'title' => __d('translate', 'View Shadow Table')],
										) ?>
									<?php } else { ?>
										<?= $this->Html->link(
											'<i class="fas fa-plus"></i>',
											['action' => 'generate', $info['table']],
											['escape' => false, 'class' => 'btn btn-sm btn-outline-success', 'title' => __d('translate', 'Generate Migration')],
										) ?>
									<?php } ?>
								</td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
<?php } ?>

<!-- Existing Shadow Tables -->
<?php if (!empty($shadowTables)) { ?>
<div class="row mb-3">
	<div class="col-12">
		<div class="card">
			<div class="card-header bg-info text-white">
				<h6 class="mb-0"><i class="fas fa-table"></i> <?= __d('translate', 'Existing Shadow Tables (_i18n)') ?></h6>
			</div>
			<div class="card-body p-0">
				<div class="table-responsive">
					<table class="table table-hover table-sm mb-0">
						<thead class="table-light">
							<tr>
								<th><?= __d('translate', 'Shadow Table') ?></th>
								<th><?= __d('translate', 'Base Table') ?></th>
								<th><?= __d('translate', 'Strategy') ?></th>
								<th class="text-center"><?= __d('translate', 'Translations') ?></th>
								<th><?= __d('translate', 'Status') ?></th>
								<th><?= __d('translate', 'Actions') ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($shadowTables as $baseTable => $info) { ?>
							<tr>
								<td><code><?= h($info['shadow_table']) ?></code></td>
								<td>
									<?php if ($info['exists']) { ?>
										<strong><?= h($info['base_table']) ?></strong>
									<?php } else { ?>
										<span class="text-muted"><?= h($info['base_table']) ?></span>
									<?php } ?>
								</td>
								<td>
									<?php if (isset($translationStrategies[$baseTable])) { ?>
										<span class="badge bg-<?= $translationStrategies[$baseTable] === 'eav' ? 'primary' : 'secondary' ?>">
											<?= h(strtoupper($translationStrategies[$baseTable])) ?>
										</span>
									<?php } ?>
								</td>
								<td class="text-center">
									<span class="badge bg-primary"><?= number_format($info['row_count']) ?></span>
								</td>
								<td>
									<?php if ($info['exists']) { ?>
										<span class="badge bg-success"><i class="fas fa-check"></i> <?= __d('translate', 'Active') ?></span>
									<?php } else { ?>
										<span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle"></i> <?= __d('translate', 'Orphaned') ?></span>
									<?php } ?>
								</td>
								<td>
									<?= $this->Html->link(
										'<i class="fas fa-eye"></i>',
										['action' => 'view', $info['shadow_table']],
										['escape' => false, 'class' => 'btn btn-sm btn-outline-primary', 'title' => __d('translate', 'View Details')],
									) ?>
								</td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
<?php } ?>

<!-- Candidate Tables -->
<?php if (!empty($candidateTables)) { ?>
<div class="row mb-3">
	<div class="col-12">
		<div class="card">
			<div class="card-header bg-warning text-dark">
				<h6 class="mb-0">
					<i class="fas fa-lightbulb"></i> <?= __d('translate', 'Tables That Could Use Translation') ?>
					<small class="float-end"><?= __d('translate', 'No shadow table yet') ?></small>
				</h6>
			</div>
			<div class="card-body p-0">
				<div class="table-responsive">
					<table class="table table-hover table-sm mb-0">
						<thead class="table-light">
							<tr>
								<th><?= __d('translate', 'Table') ?></th>
								<th><?= __d('translate', 'Translatable Fields') ?></th>
								<th class="text-center"><?= __d('translate', 'Field Count') ?></th>
								<th><?= __d('translate', 'Actions') ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($candidateTables as $info) { ?>
							<tr>
								<td><strong><?= h($info['table']) ?></strong></td>
								<td>
									<?php foreach (array_slice($info['text_fields'], 0, 5) as $field) { ?>
										<span class="badge bg-light text-dark"><?= h($field['name']) ?></span>
									<?php } ?>
									<?php if (count($info['text_fields']) > 5) { ?>
										<span class="badge bg-secondary">+<?= count($info['text_fields']) - 5 ?> more</span>
									<?php } ?>
								</td>
								<td class="text-center"><?= $info['field_count'] ?></td>
								<td>
									<?= $this->Html->link(
										'<i class="fas fa-magic"></i> ' . __d('translate', 'Generate Migration'),
										['action' => 'generate', $info['table']],
										['escape' => false, 'class' => 'btn btn-sm btn-success'],
									) ?>
								</td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
<?php } ?>

<!-- Quick Action Card -->
<div class="row">
	<div class="col-12">
		<div class="card border-primary">
			<div class="card-header bg-primary text-white">
				<h6 class="mb-0"><i class="fas fa-info-circle"></i> <?= __d('translate', 'About CakePHP TranslateBehavior') ?></h6>
			</div>
			<div class="card-body">
				<p>
					<?= __d('translate', 'CakePHP\'s TranslateBehavior allows you to translate database fields by storing translations in shadow tables with the "_i18n" suffix.') ?>
				</p>

				<h6><?= __d('translate', 'Translation Strategies') ?>:</h6>
				<ul>
					<li>
						<strong>EAV (Entity-Attribute-Value)</strong>:
						<?= __d('translate', 'Default strategy. Stores each translated field as a separate row with columns: locale, field, content.') ?>
					</li>
					<li>
						<strong>Shadow Table</strong>:
						<?= __d('translate', 'Creates separate columns for each translated field. Better performance but less flexible.') ?>
					</li>
				</ul>

				<div class="alert alert-info mt-3 mb-0">
					<i class="fas fa-book"></i>
					<?= __d('translate', 'Learn more:') ?>
					<a href="https://book.cakephp.org/5/en/orm/behaviors/translate.html" target="_blank">
						<?= __d('translate', 'CakePHP TranslateBehavior Documentation') ?>
					</a>
				</div>
			</div>
		</div>
	</div>
</div>
