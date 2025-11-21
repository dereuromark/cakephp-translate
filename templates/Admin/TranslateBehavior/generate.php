<?php
/**
 * @var \App\View\AppView $this
 * @var string|null $tableName
 * @var array $translatableFields
 * @var string|null $migrationCode
 * @var string|null $migrationName
 * @var array $selectedFields
 * @var string $strategy
 * @var array|null $availableTables
 */
?>

<?php if (!isset($tableName)) { ?>
	<!-- Table Selection -->
	<div class="row">
		<div class="col-12">
			<div class="card">
				<div class="card-header bg-primary text-white">
					<h5 class="mb-0">
						<i class="fas fa-magic"></i> <?= __d('translate', 'Generate TranslateBehavior Migration') ?>
					</h5>
				</div>
				<div class="card-body">
					<p><?= __d('translate', 'Select a table to generate a shadow table migration for CakePHP TranslateBehavior.') ?></p>

					<div class="list-group">
						<?php foreach ($availableTables as $table) { ?>
							<?= $this->Html->link(
								'<i class="fas fa-table"></i> ' . h($table),
								['action' => 'generate', $table],
								['escape' => false, 'class' => 'list-group-item list-group-item-action'],
							) ?>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php } else { ?>
	<!-- Migration Generator Form -->
	<nav aria-label="breadcrumb">
		<ol class="breadcrumb">
			<li class="breadcrumb-item">
				<?= $this->Html->link(__d('translate', 'TranslateBehavior'), ['action' => 'index']) ?>
			</li>
			<li class="breadcrumb-item">
				<?= $this->Html->link(__d('translate', 'Generate Migration'), ['action' => 'generate']) ?>
			</li>
			<li class="breadcrumb-item active" aria-current="page"><?= h($tableName) ?></li>
		</ol>
	</nav>

	<div class="row mb-3">
		<div class="col-12">
			<div class="card">
				<div class="card-header bg-primary text-white">
					<h5 class="mb-0">
						<i class="fas fa-table"></i> <?= __d('translate', 'Generate Migration for Table: {0}', h($tableName)) ?>
					</h5>
				</div>
				<div class="card-body">
					<p>
						<?= __d('translate', 'This will create a shadow table named') ?>
						<code><?= h($tableName) ?>_i18n</code>
						<?= __d('translate', 'to store translations for the selected fields.') ?>
					</p>
				</div>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-md-4">
			<div class="card">
				<div class="card-header bg-dark text-white">
					<h6 class="mb-0"><i class="fas fa-cogs"></i> <?= __d('translate', 'Configuration') ?></h6>
				</div>
				<div class="card-body">
					<?= $this->Form->create(null, ['type' => 'post']) ?>

					<!-- Strategy Selection -->
					<div class="mb-3">
						<label class="form-label fw-bold"><?= __d('translate', 'Translation Strategy') ?></label>
						<?= $this->Form->control('strategy', [
							'type' => 'radio',
							'options' => [
								'shadow_table' => __d('translate', 'Shadow Table'),
								'eav' => __d('translate', 'EAV (Entity-Attribute-Value)'),
							],
							'label' => false,
							'legend' => false,
							'templates' => [
								'radioWrapper' => '<div class="form-check mb-2">{{label}}</div>',
								'nestingLabel' => '<label class="form-check-label">{{input}}{{text}}</label>',
								'radio' => '<input type="radio" name="{{name}}" value="{{value}}"{{attrs}} class="form-check-input">',
							],
							'default' => 'shadow_table',
						]) ?>

						<div class="alert alert-info mt-2" style="font-size: 0.85rem;">
							<strong>Shadow Table:</strong> <?= __d('translate', 'Creates columns for each field. Better performance, recommended.') ?><br>
							<strong>EAV:</strong> <?= __d('translate', 'Flexible, stores each field translation as a row. More flexible but slower.') ?>
						</div>
					</div>

					<hr>

					<!-- Field Selection -->
					<div class="mb-3">
						<label class="form-label fw-bold"><?= __d('translate', 'Select Fields to Translate') ?></label>

						<?php if (empty($translatableFields)) { ?>
							<div class="alert alert-warning">
								<i class="fas fa-exclamation-triangle"></i>
								<?= __d('translate', 'No translatable fields found in this table.') ?>
							</div>
						<?php } else { ?>
							<div class="border rounded p-2" style="max-height: 300px; overflow-y: auto;">
								<?php foreach ($translatableFields as $field) { ?>
									<div class="form-check">
										<input type="checkbox"
											name="fields[]"
											value="<?= h($field['name']) ?>"
											id="field-<?= h($field['name']) ?>"
											class="form-check-input"
											<?= in_array($field['name'], $selectedFields) ? 'checked' : '' ?>>
										<label class="form-check-label" for="field-<?= h($field['name']) ?>">
											<strong><?= h($field['name']) ?></strong>
											<small class="text-muted">(<?= h($field['type']) ?><?= $field['length'] ? ', ' . $field['length'] : '' ?>)</small>
										</label>
									</div>
								<?php } ?>
							</div>

							<div class="d-grid gap-2 mt-3">
								<button type="button" class="btn btn-sm btn-outline-secondary" onclick="selectAll()">
									<i class="fas fa-check-square"></i> <?= __d('translate', 'Select All') ?>
								</button>
								<button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAll()">
									<i class="fas fa-square"></i> <?= __d('translate', 'Deselect All') ?>
								</button>
							</div>
						<?php } ?>
					</div>

					<?php if (!empty($translatableFields)) { ?>
						<hr>
						<?= $this->Form->button(
							'<i class="fas fa-magic"></i> ' . __d('translate', 'Generate Migration'),
							['type' => 'submit', 'class' => 'btn btn-primary w-100', 'escapeTitle' => false],
						) ?>
					<?php } ?>

					<?= $this->Form->end() ?>
				</div>
			</div>

			<!-- Next Steps Card -->
			<?php if ($migrationCode) { ?>
			<div class="card mt-3 border-success">
				<div class="card-header bg-success text-white">
					<h6 class="mb-0"><i class="fas fa-check-circle"></i> <?= __d('translate', 'Next Steps') ?></h6>
				</div>
				<div class="card-body">
					<ol class="mb-0" style="font-size: 0.9rem;">
						<li><?= __d('translate', 'Copy the migration code') ?></li>
						<li>
							<?= __d('translate', 'Save to') ?>
							<br><code style="font-size: 0.75rem;">config/Migrations/<br><?= h($migrationName) ?>.php</code>
						</li>
						<li>
							<?= __d('translate', 'Run:') ?>
							<br><code>bin/cake migrations migrate</code>
						</li>
						<li>
							<?= __d('translate', 'Add to your Table class:') ?>
							<pre style="font-size: 0.75rem; background: #f8f9fa; padding: 0.5rem; margin-top: 0.5rem;"><code><?php
// Format fields array with proper indentation
$fields = array_values($selectedFields);
$fieldsFormatted = "[\n";
foreach ($fields as $field) {
    $fieldsFormatted .= "            '" . $field . "',\n";
}
$fieldsFormatted .= "        ]";
?>public function initialize(array $config): void
{
    parent::initialize($config);

    $this->addBehavior('Translate', [
        'fields' => <?= $fieldsFormatted ?>,
    ]);
}</code></pre>
						</li>
					</ol>
				</div>
			</div>
			<?php } ?>
		</div>

		<div class="col-md-8">
			<?php if ($migrationCode) { ?>
				<!-- Generated Migration Code -->
				<div class="card">
					<div class="card-header bg-success text-white">
						<h6 class="mb-0">
							<i class="fas fa-file-code"></i> <?= __d('translate', 'Generated Migration Code') ?>
							<span class="float-end"><code class="text-white"><?= h($migrationName) ?>.php</code></span>
						</h6>
					</div>
					<div class="card-body p-0">
						<pre class="mb-0 p-3" style="background: #2d2d2d; color: #f8f8f2; border-radius: 0; max-height: 600px; overflow-y: auto;"><code><?= h($migrationCode) ?></code></pre>
					</div>
					<div class="card-footer">
						<button class="btn btn-sm btn-outline-primary" onclick="copyToClipboard()">
							<i class="fas fa-copy"></i> <?= __d('translate', 'Copy to Clipboard') ?>
						</button>
						<?= $this->Form->create(null, [
							'url' => ['action' => 'saveMigration'],
							'style' => 'display: inline-block; margin-left: 5px;',
						]) ?>
							<?= $this->Form->hidden('table_name', ['value' => $tableName]) ?>
							<?= $this->Form->hidden('migration_name', ['value' => $migrationName]) ?>
							<?= $this->Form->hidden('migration_code', ['value' => $migrationCode]) ?>
							<?= $this->Form->button(
								'<i class="fas fa-save"></i> ' . __d('translate', 'Save Migration File'),
								['type' => 'submit', 'class' => 'btn btn-sm btn-success', 'escapeTitle' => false],
							) ?>
						<?= $this->Form->end() ?>
						<?= $this->Html->link(
							'<i class="fas fa-arrow-left"></i> ' . __d('translate', 'Back to Overview'),
							['action' => 'index'],
							['escape' => false, 'class' => 'btn btn-sm btn-outline-secondary'],
						) ?>
					</div>
				</div>
			<?php } else { ?>
				<!-- Placeholder -->
				<div class="card">
					<div class="card-body text-center text-muted py-5">
						<i class="fas fa-arrow-left fa-3x mb-3"></i>
						<h5><?= __d('translate', 'Configure and Generate') ?></h5>
						<p><?= __d('translate', 'Select the fields you want to translate and click "Generate Migration".') ?></p>
					</div>
				</div>
			<?php } ?>
		</div>
	</div>
<?php } ?>

<script>
function selectAll() {
	document.querySelectorAll('input[name="fields[]"]').forEach(el => el.checked = true);
}

function deselectAll() {
	document.querySelectorAll('input[name="fields[]"]').forEach(el => el.checked = false);
}

function copyToClipboard() {
	const code = document.querySelector('pre code').textContent;
	navigator.clipboard.writeText(code).then(() => {
		alert('<?= __d('translate', 'Migration code copied to clipboard!') ?>');
	}).catch(err => {
		console.error('Failed to copy:', err);
	});
}
</script>
