<?php
/**
 * @var \App\View\AppView $this
 * @var string $tableName
 * @var string $baseTableName
 * @var \Cake\ORM\Entity $baseRecord
 * @var \Cake\ORM\Entity $translation
 * @var string $locale
 * @var array<string> $translatedFields
 * @var bool $hasAutoField
 * @var string|null $displayField
 */

$isNew = $translation->isNew();
?>
<nav aria-label="breadcrumb">
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<?= $this->Html->link(__d('translate', 'I18n Entries'), ['action' => 'index']) ?>
		</li>
		<li class="breadcrumb-item">
			<?= $this->Html->link(h($baseTableName), ['action' => 'entries', $tableName]) ?>
		</li>
		<li class="breadcrumb-item">
			<?= $this->Html->link(
				__d('translate', 'Record #{0}', $baseRecord->id),
				['action' => 'viewRecord', $tableName, $baseRecord->id],
			) ?>
		</li>
		<li class="breadcrumb-item active">
			<?= $isNew ? __d('translate', 'Add {0}', $locale) : __d('translate', 'Edit {0}', $locale) ?>
		</li>
	</ol>
</nav>

<div class="row">
	<div class="col-lg-8">
		<div class="card">
			<div class="card-header bg-<?= $isNew ? 'success' : 'primary' ?> text-white">
				<h5 class="mb-0">
					<i class="fas fa-<?= $isNew ? 'plus' : 'edit' ?>"></i>
					<?= $isNew ? __d('translate', 'Add Translation') : __d('translate', 'Edit Translation') ?>
					<span class="badge bg-light text-dark"><?= h($locale) ?></span>
				</h5>
			</div>
			<div class="card-body">
				<?= $this->Form->create($translation) ?>

				<?php foreach ($translatedFields as $field) { ?>
					<div class="mb-3">
						<label class="form-label">
							<?= h(ucfirst($field)) ?>
							<small class="text-muted">(<?= h($locale) ?>)</small>
						</label>
						<?php
						// Determine input type based on field name or content length
						$baseValue = $baseRecord->$field ?? '';
						$isLongText = strlen($baseValue) > 100 || in_array($field, ['description', 'content', 'body', 'text', 'meta_description']);

						if ($isLongText) {
							echo $this->Form->textarea($field, [
								'class' => 'form-control',
								'rows' => 4,
							]);
						} else {
							echo $this->Form->text($field, [
								'class' => 'form-control',
							]);
						}
						?>

						<?php if ($baseValue) { ?>
							<div class="form-text">
								<strong><?= __d('translate', 'Original') ?>:</strong>
								<?php
								if (strlen($baseValue) > 150) {
									echo h(substr($baseValue, 0, 147)) . '...';
								} else {
									echo h($baseValue);
								}
								?>
							</div>
						<?php } ?>
					</div>
				<?php } ?>

				<?php if ($hasAutoField) { ?>
					<div class="mb-3">
						<?= $this->Form->control('auto', [
							'type' => 'checkbox',
							'label' => __d('translate', 'Mark as auto-translated'),
							'class' => 'form-check-input',
						]) ?>
						<small class="text-muted d-block">
							<?= __d('translate', 'If checked, this translation is marked as machine-translated. It will be unchecked automatically when you manually edit the content.') ?>
						</small>
					</div>
				<?php } ?>

				<div class="mb-3">
					<?= $this->Form->button(
						'<i class="fas fa-save"></i> ' . __d('translate', 'Save Translation'),
						['class' => 'btn btn-primary', 'escapeTitle' => false],
					) ?>
					<?= $this->Html->link(
						__d('translate', 'Cancel'),
						['action' => 'viewRecord', $tableName, $baseRecord->id],
						['class' => 'btn btn-secondary'],
					) ?>
				</div>

				<?= $this->Form->end() ?>
			</div>
		</div>
	</div>

	<div class="col-lg-4">
		<div class="card">
			<div class="card-header">
				<h6 class="mb-0">
					<i class="fas fa-info-circle"></i> <?= __d('translate', 'Base Record') ?>
				</h6>
			</div>
			<div class="card-body">
				<dl class="mb-0">
					<dt><?= __d('translate', 'Table') ?></dt>
					<dd><code><?= h($baseTableName) ?></code></dd>

					<dt><?= __d('translate', 'ID') ?></dt>
					<dd><?= h($baseRecord->id) ?></dd>

					<?php if ($displayField && $baseRecord->$displayField) { ?>
						<dt><?= h(ucfirst($displayField)) ?></dt>
						<dd><?= h($baseRecord->$displayField) ?></dd>
					<?php } ?>

					<dt><?= __d('translate', 'Target Locale') ?></dt>
					<dd><span class="badge bg-primary"><?= h($locale) ?></span></dd>
				</dl>
			</div>
		</div>

		<div class="card mt-3">
			<div class="card-header">
				<h6 class="mb-0">
					<i class="fas fa-file-alt"></i> <?= __d('translate', 'Original Content') ?>
				</h6>
			</div>
			<div class="card-body">
				<?php foreach ($translatedFields as $field) { ?>
					<div class="mb-2">
						<strong><?= h(ucfirst($field)) ?>:</strong>
						<div class="border rounded p-2 bg-light" style="max-height: 150px; overflow-y: auto;">
							<?php
							$value = $baseRecord->$field ?? '';
							if ($value) {
								echo nl2br(h($value));
							} else {
								echo '<span class="text-muted">(' . __d('translate', 'empty') . ')</span>';
							}
							?>
						</div>
					</div>
				<?php } ?>
			</div>
		</div>
	</div>
</div>
