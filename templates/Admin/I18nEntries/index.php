<?php
/**
 * @var \App\View\AppView $this
 * @var array<string, array<string, mixed>> $shadowTables
 * @var array<string> $locales
 */
?>
<nav aria-label="breadcrumb">
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<?= $this->Html->link(__d('translate', 'Translate'), ['controller' => 'Translate', 'action' => 'index']) ?>
		</li>
		<li class="breadcrumb-item active"><?= __d('translate', 'I18n Entries') ?></li>
	</ol>
</nav>

<div class="row mb-4">
	<div class="col-12">
		<div class="card">
			<div class="card-header bg-primary text-white">
				<h5 class="mb-0">
					<i class="fas fa-database"></i> <?= __d('translate', 'TranslateBehavior Shadow Tables') ?>
				</h5>
			</div>
			<div class="card-body">
				<p class="text-muted">
					<?= __d('translate', 'Manage translations stored in TranslateBehavior shadow tables (*_i18n). These are dynamic content translations stored in the database.') ?>
				</p>

				<?php if (!empty($locales)) { ?>
					<div class="mb-3">
						<strong><?= __d('translate', 'Available Locales') ?>:</strong>
						<?php foreach ($locales as $locale) { ?>
							<span class="badge bg-secondary"><?= h($locale) ?></span>
						<?php } ?>
					</div>
				<?php } ?>
			</div>
		</div>
	</div>
</div>

<?php if (empty($shadowTables)) { ?>
	<div class="alert alert-info">
		<i class="fas fa-info-circle"></i>
		<?= __d('translate', 'No TranslateBehavior shadow tables found. Create them using the TranslateBehavior generator.') ?>
		<?= $this->Html->link(
			__d('translate', 'Go to Generator'),
			['controller' => 'TranslateBehavior', 'action' => 'index'],
			['class' => 'alert-link'],
		) ?>
	</div>
<?php } else { ?>
	<div class="row">
		<?php foreach ($shadowTables as $tableName => $info) { ?>
			<div class="col-md-6 col-lg-4 mb-4">
				<div class="card h-100 <?= $info['row_count'] > 0 ? '' : 'border-warning' ?>">
					<div class="card-header">
						<h6 class="mb-0">
							<i class="fas fa-table"></i>
							<?= h($info['base_table']) ?>
							<?php if (!$info['base_exists']) { ?>
								<span class="badge bg-danger" title="<?= __d('translate', 'Base table missing') ?>">
									<i class="fas fa-exclamation-triangle"></i>
								</span>
							<?php } ?>
						</h6>
					</div>
					<div class="card-body">
						<dl class="row mb-0">
							<dt class="col-6"><?= __d('translate', 'Shadow Table') ?>:</dt>
							<dd class="col-6"><code><?= h($tableName) ?></code></dd>

							<dt class="col-6"><?= __d('translate', 'Strategy') ?>:</dt>
							<dd class="col-6">
								<span class="badge bg-<?= $info['strategy'] === 'eav' ? 'primary' : 'secondary' ?>">
									<?= h(strtoupper($info['strategy'])) ?>
								</span>
							</dd>

							<dt class="col-6"><?= __d('translate', 'Entries') ?>:</dt>
							<dd class="col-6">
								<span class="badge bg-<?= $info['row_count'] > 0 ? 'success' : 'warning' ?>">
									<?= number_format($info['row_count']) ?>
								</span>
							</dd>

							<?php if ($info['has_auto_field']) { ?>
								<dt class="col-6"><?= __d('translate', 'Auto/Manual') ?>:</dt>
								<dd class="col-6">
									<span class="badge bg-info" title="<?= __d('translate', 'Auto-translated') ?>">
										<i class="fas fa-robot"></i> <?= number_format($info['auto_count']) ?>
									</span>
									<span class="badge bg-secondary" title="<?= __d('translate', 'Manual') ?>">
										<i class="fas fa-user"></i> <?= number_format($info['manual_count']) ?>
									</span>
								</dd>
							<?php } else { ?>
								<dt class="col-6"><?= __d('translate', 'Auto Field') ?>:</dt>
								<dd class="col-6">
									<span class="badge bg-warning text-dark">
										<i class="fas fa-exclamation-triangle"></i> <?= __d('translate', 'Missing') ?>
									</span>
								</dd>
							<?php } ?>
						</dl>
					</div>
					<div class="card-footer">
						<?= $this->Html->link(
							'<i class="fas fa-list"></i> ' . __d('translate', 'View Entries'),
							['action' => 'entries', $tableName],
							['class' => 'btn btn-primary btn-sm', 'escape' => false],
						) ?>
						<?= $this->Html->link(
							'<i class="fas fa-cog"></i>',
							['controller' => 'TranslateBehavior', 'action' => 'view', $tableName],
							['class' => 'btn btn-outline-secondary btn-sm', 'escape' => false, 'title' => __d('translate', 'Table Details')],
						) ?>
					</div>
				</div>
			</div>
		<?php } ?>
	</div>
<?php } ?>

<div class="row mt-4">
	<div class="col-12">
		<div class="card border-info">
			<div class="card-header bg-info text-white">
				<h6 class="mb-0"><i class="fas fa-lightbulb"></i> <?= __d('translate', 'Tips') ?></h6>
			</div>
			<div class="card-body">
				<ul class="mb-0">
					<li>
						<strong><?= __d('translate', 'Auto Field') ?>:</strong>
						<?= __d('translate', 'Tables with the "auto" field can track which translations were machine-translated. Add it via migration to enable this feature.') ?>
					</li>
					<li>
						<strong><?= __d('translate', 'EAV vs Shadow Table') ?>:</strong>
						<?= __d('translate', 'EAV stores all fields in a single table with field/content columns. Shadow Table stores each field as a column.') ?>
					</li>
					<li>
						<strong><?= __d('translate', 'Glossary') ?>:</strong>
						<?= __d('translate', 'Existing PO translations can be used as a glossary to suggest consistent translations for common terms.') ?>
					</li>
				</ul>
			</div>
		</div>
	</div>
</div>
