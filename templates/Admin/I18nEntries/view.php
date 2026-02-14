<?php
/**
 * @var \App\View\AppView $this
 * @var string $tableName
 * @var string $baseTableName
 * @var \Cake\Datasource\EntityInterface $entry
 * @var string $strategy
 * @var array<string> $translatedFields
 * @var bool $hasAutoField
 * @var \Cake\Datasource\EntityInterface|null $baseRecord
 * @var array<array<string, string>> $glossarySuggestions
 */
?>
<nav aria-label="breadcrumb">
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<?= $this->Html->link(__d('translate', 'I18n Entries'), ['action' => 'index']) ?>
		</li>
		<li class="breadcrumb-item">
			<?= $this->Html->link(h($baseTableName), ['action' => 'entries', $tableName]) ?>
		</li>
		<li class="breadcrumb-item active">#<?= h($entry->id) ?></li>
	</ol>
</nav>

<div class="row">
	<div class="col-lg-8">
		<!-- Entry Details -->
		<div class="card mb-4">
			<div class="card-header bg-primary text-white">
				<h5 class="mb-0">
					<i class="fas fa-language"></i> <?= __d('translate', 'Translation Entry') ?> #<?= h($entry->id) ?>
				</h5>
			</div>
			<div class="card-body">
				<dl class="row">
					<dt class="col-sm-3"><?= __d('translate', 'ID') ?>:</dt>
					<dd class="col-sm-9"><?= h($entry->id) ?></dd>

					<dt class="col-sm-3"><?= __d('translate', 'Locale') ?>:</dt>
					<dd class="col-sm-9">
						<span class="badge bg-primary fs-6"><?= h($entry->locale) ?></span>
					</dd>

					<dt class="col-sm-3"><?= __d('translate', 'Foreign Key') ?>:</dt>
					<dd class="col-sm-9"><?= h($entry->foreign_key) ?></dd>

					<?php if ($strategy === 'eav') { ?>
						<dt class="col-sm-3"><?= __d('translate', 'Model') ?>:</dt>
						<dd class="col-sm-9"><?= h($entry->model ?? '-') ?></dd>

						<dt class="col-sm-3"><?= __d('translate', 'Field') ?>:</dt>
						<dd class="col-sm-9">
							<span class="badge bg-info"><?= h($entry->field) ?></span>
						</dd>

						<dt class="col-sm-3"><?= __d('translate', 'Content') ?>:</dt>
						<dd class="col-sm-9">
							<div class="border rounded p-3 bg-light">
								<?= nl2br(h($entry->content)) ?>
							</div>
						</dd>
					<?php } else { ?>
						<?php foreach ($translatedFields as $field) { ?>
							<dt class="col-sm-3"><?= h(ucfirst($field)) ?>:</dt>
							<dd class="col-sm-9">
								<div class="border rounded p-3 bg-light">
									<?= nl2br(h($entry->$field ?? '')) ?>
								</div>
							</dd>
						<?php } ?>
					<?php } ?>

					<?php if ($hasAutoField) { ?>
						<dt class="col-sm-3"><?= __d('translate', 'Translation Type') ?>:</dt>
						<dd class="col-sm-9">
							<?php if ($entry->auto) { ?>
								<span class="badge bg-info fs-6">
									<i class="fas fa-robot"></i> <?= __d('translate', 'Auto-translated') ?>
								</span>
							<?php } else { ?>
								<span class="badge bg-secondary fs-6">
									<i class="fas fa-user"></i> <?= __d('translate', 'Manual') ?>
								</span>
							<?php } ?>
						</dd>
					<?php } ?>
				</dl>
			</div>
			<div class="card-footer">
				<?= $this->Html->link(
					'<i class="fas fa-edit"></i> ' . __d('translate', 'Edit'),
					['action' => 'edit', $tableName, $entry->id],
					['class' => 'btn btn-primary', 'escape' => false],
				) ?>
				<?= $this->Html->link(
					'<i class="fas fa-arrow-left"></i> ' . __d('translate', 'Back'),
					['action' => 'entries', $tableName],
					['class' => 'btn btn-outline-secondary', 'escape' => false],
				) ?>
			</div>
		</div>

		<!-- Base Record Info -->
		<?php if ($baseRecord) { ?>
			<div class="card mb-4">
				<div class="card-header bg-secondary text-white">
					<h6 class="mb-0">
						<i class="fas fa-database"></i> <?= __d('translate', 'Source Record') ?> (<?= h($baseTableName) ?> #<?= h($entry->foreign_key) ?>)
					</h6>
				</div>
				<div class="card-body">
					<dl class="row mb-0">
						<?php foreach ($baseRecord->toArray() as $field => $value) { ?>
							<?php if (!is_array($value) && !is_object($value)) { ?>
								<dt class="col-sm-3"><?= h($field) ?>:</dt>
								<dd class="col-sm-9">
									<?php
									if (is_null($value)) {
										echo '<span class="text-muted">NULL</span>';
									} elseif (strlen((string)$value) > 200) {
										echo h(substr((string)$value, 0, 197)) . '...';
									} else {
										echo h($value);
									}
									?>
								</dd>
							<?php } ?>
						<?php } ?>
					</dl>
				</div>
			</div>
		<?php } ?>
	</div>

	<div class="col-lg-4">
		<!-- Glossary Suggestions -->
		<?php if (!empty($glossarySuggestions)) { ?>
			<div class="card mb-4">
				<div class="card-header bg-info text-white">
					<h6 class="mb-0">
						<i class="fas fa-book"></i> <?= __d('translate', 'Glossary Suggestions') ?>
					</h6>
				</div>
				<div class="card-body p-0">
					<ul class="list-group list-group-flush">
						<?php foreach ($glossarySuggestions as $suggestion) { ?>
							<li class="list-group-item">
								<small class="text-muted"><?= h($suggestion['source']) ?></small>
								<br>
								<strong><?= h($suggestion['translation']) ?></strong>
							</li>
						<?php } ?>
					</ul>
				</div>
				<div class="card-footer text-muted small">
					<?= __d('translate', 'Suggestions from existing PO translations') ?>
				</div>
			</div>
		<?php } ?>

		<!-- Quick Actions -->
		<div class="card">
			<div class="card-header">
				<h6 class="mb-0"><i class="fas fa-bolt"></i> <?= __d('translate', 'Quick Actions') ?></h6>
			</div>
			<div class="card-body">
				<div class="d-grid gap-2">
					<?php if ($hasAutoField) { ?>
						<?= $this->Form->postLink(
							$entry->auto
								? '<i class="fas fa-user"></i> ' . __d('translate', 'Mark as Manual')
								: '<i class="fas fa-robot"></i> ' . __d('translate', 'Mark as Auto'),
							['action' => 'batchUpdateAuto', $tableName, '?' => ['entry_ids' => [$entry->id], 'auto' => !$entry->auto]],
							[
								'class' => 'btn btn-outline-' . ($entry->auto ? 'warning' : 'info'),
								'escape' => false,
								'block' => true,
							],
						) ?>
					<?php } ?>
					<?= $this->Form->postLink(
						'<i class="fas fa-trash"></i> ' . __d('translate', 'Delete'),
						['action' => 'delete', $tableName, $entry->id],
						[
							'class' => 'btn btn-outline-danger',
							'escape' => false,
							'confirm' => __d('translate', 'Are you sure you want to delete this entry?'),
							'block' => true,
						],
					) ?>
				</div>
			</div>
		</div>
	</div>
</div>
