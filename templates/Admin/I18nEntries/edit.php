<?php
/**
 * @var \App\View\AppView $this
 * @var string $tableName
 * @var string $baseTableName
 * @var \Cake\Datasource\EntityInterface $entry
 * @var string $strategy
 * @var array<string> $translatedFields
 * @var bool $hasAutoField
 * @var array<string> $locales
 * @var string|null $sourceText
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
		<li class="breadcrumb-item active"><?= __d('translate', 'Edit') ?> #<?= h($entry->id) ?></li>
	</ol>
</nav>

<div class="row">
	<div class="col-lg-8">
		<div class="card">
			<div class="card-header bg-primary text-white">
				<h5 class="mb-0">
					<i class="fas fa-edit"></i> <?= __d('translate', 'Edit Translation') ?>
				</h5>
			</div>
			<?= $this->Form->create($entry) ?>
			<div class="card-body">
				<!-- Read-only info -->
				<div class="row mb-3">
					<div class="col-md-4">
						<label class="form-label"><?= __d('translate', 'Locale') ?></label>
						<div class="form-control-plaintext">
							<span class="badge bg-primary fs-6"><?= h($entry->locale) ?></span>
						</div>
					</div>
					<div class="col-md-4">
						<label class="form-label"><?= __d('translate', 'Foreign Key') ?></label>
						<div class="form-control-plaintext"><?= h($entry->foreign_key) ?></div>
					</div>
					<?php if ($strategy === 'eav') { ?>
						<div class="col-md-4">
							<label class="form-label"><?= __d('translate', 'Field') ?></label>
							<div class="form-control-plaintext">
								<span class="badge bg-info"><?= h($entry->field) ?></span>
							</div>
						</div>
					<?php } ?>
				</div>

				<hr>

				<!-- Source text (read-only) -->
				<?php if ($sourceText) { ?>
					<div class="mb-3">
						<label class="form-label">
							<i class="fas fa-file-alt"></i> <?= __d('translate', 'Source Text') ?>
							<small class="text-muted">(<?= __d('translate', 'original') ?>)</small>
						</label>
						<div class="form-control bg-light" style="min-height: 80px; white-space: pre-wrap;">
							<?= h($sourceText) ?>
						</div>
					</div>
				<?php } ?>

				<!-- Translation field(s) -->
				<?php if ($strategy === 'eav') { ?>
					<div class="mb-3">
						<label class="form-label" for="content">
							<i class="fas fa-language"></i> <?= __d('translate', 'Translation') ?>
						</label>
						<?= $this->Form->textarea('content', [
							'class' => 'form-control',
							'rows' => 6,
							'id' => 'content',
						]) ?>
					</div>
				<?php } else { ?>
					<?php foreach ($translatedFields as $field) { ?>
						<div class="mb-3">
							<label class="form-label" for="<?= h($field) ?>">
								<i class="fas fa-language"></i> <?= h(ucfirst($field)) ?>
							</label>
							<?= $this->Form->textarea($field, [
								'class' => 'form-control',
								'rows' => 4,
								'id' => h($field),
							]) ?>
						</div>
					<?php } ?>
				<?php } ?>

				<?php if ($hasAutoField) { ?>
					<div class="mb-3">
						<div class="form-check">
							<?= $this->Form->checkbox('auto', [
								'class' => 'form-check-input',
								'id' => 'auto',
							]) ?>
							<label class="form-check-label" for="auto">
								<i class="fas fa-robot"></i> <?= __d('translate', 'Auto-translated') ?>
								<small class="text-muted d-block">
									<?= __d('translate', 'Check this if the translation was machine-generated. Uncheck if you manually edited it.') ?>
								</small>
							</label>
						</div>
					</div>
				<?php } ?>
			</div>
			<div class="card-footer">
				<?= $this->Form->button(
					'<i class="fas fa-save"></i> ' . __d('translate', 'Save'),
					['class' => 'btn btn-primary', 'escape' => false],
				) ?>
				<?= $this->Html->link(
					'<i class="fas fa-times"></i> ' . __d('translate', 'Cancel'),
					['action' => 'entries', $tableName],
					['class' => 'btn btn-outline-secondary', 'escape' => false],
				) ?>
			</div>
			<?= $this->Form->end() ?>
		</div>
	</div>

	<div class="col-lg-4">
		<!-- Auto-Translate -->
		<div class="card mb-4">
			<div class="card-header bg-info text-white">
				<h6 class="mb-0">
					<i class="fas fa-magic"></i> <?= __d('translate', 'Auto-Translate') ?>
				</h6>
			</div>
			<div class="card-body">
				<p class="text-muted small">
					<?= __d('translate', 'Use machine translation to fill in the translation. This will overwrite the current content.') ?>
				</p>
				<?= $this->Form->create(null, ['url' => ['action' => 'autoTranslate', $tableName]]) ?>
				<?= $this->Form->hidden('entry_ids[]', ['value' => $entry->id]) ?>
				<div class="mb-3">
					<?= $this->Form->control('source_locale', [
						'type' => 'select',
						'options' => ['en' => 'English', 'de' => 'Deutsch'],
						'default' => 'en',
						'label' => __d('translate', 'Source Language'),
						'class' => 'form-select form-select-sm',
					]) ?>
				</div>
				<?= $this->Form->button(
					'<i class="fas fa-language"></i> ' . __d('translate', 'Translate Now'),
					['class' => 'btn btn-info w-100', 'escape' => false],
				) ?>
				<?= $this->Form->end() ?>
			</div>
		</div>

		<!-- Glossary Suggestions -->
		<?php if (!empty($glossarySuggestions)) { ?>
			<div class="card mb-4">
				<div class="card-header">
					<h6 class="mb-0">
						<i class="fas fa-book"></i> <?= __d('translate', 'Glossary') ?>
					</h6>
				</div>
				<div class="card-body p-0">
					<ul class="list-group list-group-flush">
						<?php foreach ($glossarySuggestions as $suggestion) { ?>
							<li class="list-group-item small">
								<div class="d-flex justify-content-between">
									<span class="text-muted"><?= h($suggestion['source']) ?></span>
									<button type="button" class="btn btn-sm btn-outline-primary copy-suggestion"
											data-text="<?= h($suggestion['translation']) ?>"
											title="<?= __d('translate', 'Copy to clipboard') ?>">
										<i class="fas fa-copy"></i>
									</button>
								</div>
								<strong><?= h($suggestion['translation']) ?></strong>
							</li>
						<?php } ?>
					</ul>
				</div>
				<div class="card-footer text-muted small">
					<?= __d('translate', 'Click to copy translations from PO files') ?>
				</div>
			</div>
		<?php } ?>

		<!-- Tips -->
		<div class="card border-light">
			<div class="card-header">
				<h6 class="mb-0"><i class="fas fa-lightbulb"></i> <?= __d('translate', 'Tips') ?></h6>
			</div>
			<div class="card-body small text-muted">
				<ul class="mb-0">
					<li><?= __d('translate', 'Manually editing the translation will automatically uncheck the "Auto-translated" flag.') ?></li>
					<li><?= __d('translate', 'Use glossary suggestions for consistent terminology.') ?></li>
					<li><?= __d('translate', 'Machine translations can be refined by editing and unchecking the auto flag.') ?></li>
				</ul>
			</div>
		</div>
	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
	// Copy suggestion to clipboard
	document.querySelectorAll('.copy-suggestion').forEach(function(btn) {
		btn.addEventListener('click', function() {
			const text = this.getAttribute('data-text');
			navigator.clipboard.writeText(text).then(function() {
				btn.innerHTML = '<i class="fas fa-check"></i>';
				setTimeout(function() {
					btn.innerHTML = '<i class="fas fa-copy"></i>';
				}, 1000);
			});
		});
	});
});
</script>
