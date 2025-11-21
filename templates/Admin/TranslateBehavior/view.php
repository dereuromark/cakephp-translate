<?php
/**
 * @var \App\View\AppView $this
 * @var string $tableName
 * @var string $baseTableName
 * @var \Cake\Database\Schema\TableSchema $schema
 * @var string $strategy
 * @var array $sampleData
 * @var array $translatedFields
 * @var array $locales
 * @var bool $baseTableExists
 * @var array|null $modelInfo
 */
?>

<nav aria-label="breadcrumb">
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<?= $this->Html->link(__d('translate', 'TranslateBehavior'), ['action' => 'index']) ?>
		</li>
		<li class="breadcrumb-item active" aria-current="page"><?= h($tableName) ?></li>
	</ol>
</nav>

<div class="row mb-3">
	<div class="col-12">
		<div class="card">
			<div class="card-header bg-dark text-white">
				<h5 class="mb-0">
					<i class="fas fa-language"></i> <?= h($tableName) ?>
					<span class="float-end">
						<span class="badge bg-<?= $strategy === 'eav' ? 'primary' : 'secondary' ?>">
							<?= h(strtoupper($strategy)) ?>
						</span>
					</span>
				</h5>
			</div>
			<div class="card-body">
				<dl class="row mb-0">
					<dt class="col-sm-3"><?= __d('translate', 'Shadow Table') ?>:</dt>
					<dd class="col-sm-9"><code><?= h($tableName) ?></code></dd>

					<dt class="col-sm-3"><?= __d('translate', 'Base Table') ?>:</dt>
					<dd class="col-sm-9">
						<code><?= h($baseTableName) ?></code>
						<?php if ($baseTableExists) { ?>
							<span class="badge bg-success ms-2"><i class="fas fa-check"></i> <?= __d('translate', 'Exists') ?></span>
						<?php } else { ?>
							<span class="badge bg-warning text-dark ms-2"><i class="fas fa-exclamation-triangle"></i> <?= __d('translate', 'Missing') ?></span>
						<?php } ?>
					</dd>

					<?php if ($modelInfo) { ?>
						<dt class="col-sm-3"><?= __d('translate', 'Model Class') ?>:</dt>
						<dd class="col-sm-9">
							<code><?= h($modelInfo['class']) ?></code>
							<?php if ($modelInfo['has_behavior']) { ?>
								<span class="badge bg-success ms-2">
									<i class="fas fa-check"></i> <?= __d('translate', 'Has TranslateBehavior') ?>
								</span>
							<?php } else { ?>
								<span class="badge bg-danger ms-2">
									<i class="fas fa-times"></i> <?= __d('translate', 'No TranslateBehavior') ?>
								</span>
							<?php } ?>
						</dd>
					<?php } ?>

					<dt class="col-sm-3"><?= __d('translate', 'Strategy') ?>:</dt>
					<dd class="col-sm-9">
						<strong><?= h(strtoupper($strategy)) ?></strong>
						<?php if ($strategy === 'eav') { ?>
							<small class="text-muted">(<?= __d('translate', 'Entity-Attribute-Value') ?>)</small>
						<?php } else { ?>
							<small class="text-muted">(<?= __d('translate', 'Shadow Table') ?>)</small>
						<?php } ?>
					</dd>

					<dt class="col-sm-3"><?= __d('translate', 'Translated Fields') ?>:</dt>
					<dd class="col-sm-9">
						<?php foreach ($translatedFields as $field) { ?>
							<span class="badge bg-info"><?= h($field) ?></span>
						<?php } ?>
					</dd>

					<?php if (!empty($locales)) { ?>
						<dt class="col-sm-3"><?= __d('translate', 'Locales') ?>:</dt>
						<dd class="col-sm-9">
							<?php foreach ($locales as $locale) { ?>
								<span class="badge bg-secondary"><?= h($locale) ?></span>
							<?php } ?>
						</dd>
					<?php } ?>
				</dl>
			</div>
		</div>
	</div>
</div>

<!-- Schema Information -->
<div class="row mb-3">
	<div class="col-12">
		<div class="card">
			<div class="card-header bg-primary text-white">
				<h6 class="mb-0"><i class="fas fa-columns"></i> <?= __d('translate', 'Table Schema') ?></h6>
			</div>
			<div class="card-body p-0">
				<div class="table-responsive">
					<table class="table table-sm table-hover mb-0">
						<thead class="table-light">
							<tr>
								<th><?= __d('translate', 'Column') ?></th>
								<th><?= __d('translate', 'Type') ?></th>
								<th><?= __d('translate', 'Length') ?></th>
								<th><?= __d('translate', 'Null') ?></th>
								<th><?= __d('translate', 'Default') ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($schema->columns() as $columnName) { ?>
								<?php
								$column = $schema->getColumn($columnName);
								$isPrimaryKey = in_array($columnName, $schema->getPrimaryKey());
								?>
								<tr>
									<td>
										<strong><?= h($columnName) ?></strong>
										<?php if ($isPrimaryKey) { ?>
											<span class="badge bg-warning text-dark">PK</span>
										<?php } ?>
									</td>
									<td><code><?= h($column['type']) ?></code></td>
									<td><?= $column['length'] ?? '-' ?></td>
									<td>
										<?php if ($column['null']) { ?>
											<span class="badge bg-success">YES</span>
										<?php } else { ?>
											<span class="badge bg-danger">NO</span>
										<?php } ?>
									</td>
									<td>
										<?php if ($column['default'] !== null) { ?>
											<code><?= h($column['default']) ?></code>
										<?php } else { ?>
											<span class="text-muted">NULL</span>
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

<!-- Sample Data -->
<?php if (!empty($sampleData)) { ?>
<div class="row mb-3">
	<div class="col-12">
		<div class="card">
			<div class="card-header bg-success text-white">
				<h6 class="mb-0">
					<i class="fas fa-list"></i> <?= __d('translate', 'Translation Data') ?>
					<small class="text-white-50">(<?= __d('translate', 'first 20 rows') ?>)</small>
				</h6>
			</div>
			<div class="card-body p-0">
				<div class="table-responsive">
					<table class="table table-sm table-striped table-hover mb-0" style="font-size: 0.85rem;">
						<thead class="table-light">
							<tr>
								<?php foreach (array_keys($sampleData[0] ?? []) as $columnName) { ?>
									<th><?= h($columnName) ?></th>
								<?php } ?>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($sampleData as $row) { ?>
							<tr>
								<?php foreach ($row as $columnName => $value) { ?>
									<td>
										<?php if ($value === null) { ?>
											<span class="text-muted">NULL</span>
										<?php } elseif ($columnName === 'locale') { ?>
											<span class="badge bg-primary"><?= h($value) ?></span>
										<?php } elseif ($columnName === 'field') { ?>
											<span class="badge bg-info"><?= h($value) ?></span>
										<?php } elseif ($columnName === 'content' || in_array($columnName, $translatedFields)) { ?>
											<?php if (strlen($value) > 100) { ?>
												<small><?= h(substr($value, 0, 97)) ?>...</small>
											<?php } else { ?>
												<?= h($value) ?>
											<?php } ?>
										<?php } else { ?>
											<?= h($value) ?>
										<?php } ?>
									</td>
								<?php } ?>
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

<!-- Info Box -->
<div class="row">
	<div class="col-12">
		<div class="card border-info">
			<div class="card-header bg-info text-white">
				<h6 class="mb-0"><i class="fas fa-info-circle"></i> <?= __d('translate', 'How to Use') ?></h6>
			</div>
			<div class="card-body">
				<?php if ($strategy === 'eav') { ?>
					<p><?= __d('translate', 'This table uses the EAV (Entity-Attribute-Value) strategy where:') ?></p>
					<ul>
						<li><strong>locale</strong>: <?= __d('translate', 'The language/locale code (e.g., en_US, de_DE)') ?></li>
						<li><strong>model</strong>: <?= __d('translate', 'The model name') ?></li>
						<li><strong>foreign_key</strong>: <?= __d('translate', 'ID of the record in the base table') ?></li>
						<li><strong>field</strong>: <?= __d('translate', 'Name of the translated field') ?></li>
						<li><strong>content</strong>: <?= __d('translate', 'The translated value') ?></li>
					</ul>
				<?php } else { ?>
					<p><?= __d('translate', 'This table uses the Shadow Table strategy where each translated field has its own column.') ?></p>
				<?php } ?>

				<?php if ($modelInfo && !$modelInfo['has_behavior']) { ?>
					<div class="alert alert-warning mt-3">
						<i class="fas fa-exclamation-triangle"></i>
						<strong><?= __d('translate', 'Note') ?>:</strong>
						<?= __d('translate', 'The model class exists but doesn\'t have the TranslateBehavior attached. Add it to your Table class:') ?>
						<pre class="mt-2 mb-0" style="background: #f8f9fa; padding: 0.5rem;"><code><?php
// Format fields array with proper indentation
$fields = array_values($translatedFields);
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
					</div>
				<?php } ?>
			</div>
		</div>
	</div>
</div>
