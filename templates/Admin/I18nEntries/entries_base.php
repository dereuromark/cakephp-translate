<?php
/**
 * @var \App\View\AppView $this
 * @var string $tableName
 * @var string $baseTableName
 * @var iterable $baseRecords
 * @var array<int, array<string, array<string, mixed>>> $translationStatus
 * @var array<int, array<string, bool>> $baseFieldStatus
 * @var array<string> $locales
 * @var array<string> $translatedFields
 * @var bool $hasAutoField
 * @var string $strategy
 * @var string $foreignKeyColumn
 * @var string|null $displayField
 */
?>
<nav aria-label="breadcrumb">
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<?= $this->Html->link(__d('translate', 'I18n Entries'), ['action' => 'index']) ?>
		</li>
		<li class="breadcrumb-item active"><?= h($baseTableName) ?></li>
	</ol>
</nav>

<div class="row mb-3">
	<div class="col-12">
		<div class="card">
			<div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
				<h5 class="mb-0">
					<i class="fas fa-language"></i> <?= h($baseTableName) ?>
					<small class="text-muted">(<?= h($tableName) ?>)</small>
				</h5>
				<span class="badge bg-info">
					<?= __d('translate', 'Base Table View') ?>
				</span>
			</div>
			<div class="card-body">
				<div class="row mb-3">
					<div class="col-md-8">
						<p class="text-muted mb-0">
							<i class="fas fa-info-circle"></i>
							<?= __d('translate', 'Showing records from the base table with their translation status per locale.') ?>
						</p>
					</div>
					<div class="col-md-4 text-end">
						<span class="badge bg-success"><i class="fas fa-check"></i></span> = <?= __d('translate', 'Translated') ?>
						<span class="badge bg-warning text-dark"><i class="fas fa-exclamation"></i></span> = <?= __d('translate', 'Missing') ?>
						<?php if ($hasAutoField) { ?>
							<span class="badge bg-info"><i class="fas fa-robot"></i></span> = <?= __d('translate', 'Auto') ?>
						<?php } ?>
					</div>
				</div>

				<!-- Filters -->
				<?= $this->Form->create(null, ['type' => 'get', 'class' => 'row g-3']) ?>
					<div class="col-md-4">
						<?= $this->Form->control('search', [
							'type' => 'text',
							'value' => $this->request->getQuery('search'),
							'label' => __d('translate', 'Search'),
							'placeholder' => $displayField ? __d('translate', 'Search by {0}...', $displayField) : __d('translate', 'Search...'),
							'class' => 'form-control',
						]) ?>
					</div>

					<div class="col-md-2 d-flex align-items-end">
						<?= $this->Form->button('<i class="fas fa-filter"></i> ' . __d('translate', 'Filter'), [
							'type' => 'submit',
							'class' => 'btn btn-primary',
							'escapeTitle' => false,
						]) ?>
					</div>
				<?= $this->Form->end() ?>
			</div>
		</div>
	</div>
</div>

<!-- Records Table -->
<div class="row">
	<div class="col-12">
		<?= $this->Form->create(null, ['url' => ['action' => 'autoTranslateBatch', $tableName], 'id' => 'batch-form']) ?>
		<div class="card">
			<div class="card-header d-flex justify-content-between align-items-center">
				<div>
					<input type="checkbox" id="select-all" class="form-check-input me-2">
					<label for="select-all" class="form-check-label"><?= __d('translate', 'Select All') ?></label>
				</div>
				<div class="d-flex align-items-center gap-2">
					<div class="input-group input-group-sm" style="width: auto;">
						<span class="input-group-text"><?= __d('translate', 'Locales') ?></span>
						<select name="locales[]" multiple class="form-select form-select-sm" style="min-width: 150px;" id="locale-select">
							<?php foreach ($locales as $locale) { ?>
								<option value="<?= h($locale) ?>" selected><?= h($locale) ?></option>
							<?php } ?>
						</select>
					</div>
					<button type="submit" class="btn btn-info btn-sm" onclick="return confirmBatch()">
						<i class="fas fa-magic"></i> <?= __d('translate', 'Auto-translate Selected') ?>
					</button>
				</div>
			</div>
			<div class="card-body p-0">
				<div class="table-responsive">
					<table class="table table-striped table-hover mb-0">
						<thead class="table-light">
							<tr>
								<th style="width: 40px;"></th>
								<th><?= __d('translate', 'ID') ?></th>
								<?php if ($displayField) { ?>
									<th><?= h(ucfirst($displayField)) ?></th>
								<?php } ?>
								<th><?= __d('translate', 'Translated Fields') ?></th>
								<?php foreach ($locales as $locale) { ?>
									<th class="text-center">
										<span class="badge bg-secondary"><?= h($locale) ?></span>
									</th>
								<?php } ?>
								<th class="actions"><?= __d('translate', 'Actions') ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($baseRecords as $record) { ?>
								<tr>
									<td>
										<input type="checkbox" name="record_ids[]" value="<?= h($record->id) ?>" class="form-check-input record-checkbox">
									</td>
									<td><?= h($record->id) ?></td>
									<?php if ($displayField) { ?>
										<td>
											<?php
											$value = $record->$displayField ?? '';
											if (strlen($value) > 60) {
												echo h(substr($value, 0, 57)) . '...';
											} else {
												echo h($value);
											}
											?>
										</td>
									<?php } ?>
									<td>
										<small class="text-muted">
											<?= h(implode(', ', $translatedFields)) ?>
										</small>
									</td>
									<?php foreach ($locales as $locale) { ?>
										<td class="text-center">
											<?php
											$status = $translationStatus[$record->id][$locale] ?? null;
											$baseFields = $baseFieldStatus[$record->id] ?? [];

											if ($status && $status['exists']) {
												// Only consider fields that have content in the base record
												$neededFields = array_filter($baseFields);
												$translatedCount = 0;
												$neededCount = count($neededFields);

												foreach ($neededFields as $field => $hasContent) {
													if (!empty($status['fields'][$field])) {
														$translatedCount++;
													}
												}

												$allFilled = $neededCount > 0 && $translatedCount === $neededCount;
												$partialFilled = $translatedCount > 0 && $translatedCount < $neededCount;

												if ($allFilled) {
													if ($hasAutoField && $status['auto']) {
														echo '<span class="badge bg-info" title="' . __d('translate', 'Auto-translated') . '"><i class="fas fa-robot"></i></span>';
													} else {
														echo '<span class="badge bg-success" title="' . __d('translate', 'Translated') . '"><i class="fas fa-check"></i></span>';
													}
												} elseif ($partialFilled) {
													echo '<span class="badge bg-warning text-dark" title="' . __d('translate', 'Partially translated') . '"><i class="fas fa-exclamation"></i></span>';
												} else {
													echo '<span class="badge bg-danger" title="' . __d('translate', 'Empty translation') . '"><i class="fas fa-times"></i></span>';
												}

												// Edit link
												echo ' ';
												echo $this->Html->link(
													'<i class="fas fa-edit"></i>',
													['action' => 'editTranslation', $tableName, $record->id, $locale],
													['class' => 'btn btn-sm btn-outline-secondary', 'escape' => false, 'title' => __d('translate', 'Edit {0}', $locale)],
												);
											} else {
												// No translation exists
												echo '<span class="badge bg-secondary" title="' . __d('translate', 'Not translated') . '"><i class="fas fa-minus"></i></span>';
												echo ' ';
												echo $this->Html->link(
													'<i class="fas fa-plus"></i>',
													['action' => 'addTranslation', $tableName, $record->id, $locale],
													['class' => 'btn btn-sm btn-outline-success', 'escape' => false, 'title' => __d('translate', 'Add {0}', $locale)],
												);
											}
											?>
										</td>
									<?php } ?>
									<td class="actions">
										<?= $this->Html->link(
											'<i class="fas fa-eye"></i>',
											['action' => 'viewRecord', $tableName, $record->id],
											['class' => 'btn btn-sm btn-outline-primary', 'escape' => false, 'title' => __d('translate', 'View All')],
										) ?>
									</td>
								</tr>
							<?php } ?>

							<?php if (empty(iterator_to_array($baseRecords))) { ?>
								<tr>
									<td colspan="<?= 5 + count($locales) ?>" class="text-center text-muted py-4">
										<i class="fas fa-inbox fa-2x mb-2"></i><br>
										<?= __d('translate', 'No records found.') ?>
									</td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</div>
			<div class="card-footer">
				<?= $this->element('Tools.pagination') ?>
			</div>
		</div>
		<?= $this->Form->end() ?>
	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
	const selectAll = document.getElementById('select-all');
	const checkboxes = document.querySelectorAll('.record-checkbox');

	selectAll.addEventListener('change', function() {
		checkboxes.forEach(cb => cb.checked = this.checked);
	});

	checkboxes.forEach(cb => {
		cb.addEventListener('change', function() {
			selectAll.checked = [...checkboxes].every(c => c.checked);
		});
	});
});

function confirmBatch() {
	const selected = document.querySelectorAll('.record-checkbox:checked').length;
	if (selected === 0) {
		alert('<?= __d('translate', 'Please select at least one record.') ?>');
		return false;
	}
	return confirm('<?= __d('translate', 'Auto-translate {0} selected record(s)?') ?>'.replace('{0}', selected));
}
</script>

<div class="row mt-4">
	<div class="col-12">
		<div class="card border-info">
			<div class="card-header bg-info text-white">
				<h6 class="mb-0"><i class="fas fa-lightbulb"></i> <?= __d('translate', 'Legend') ?></h6>
			</div>
			<div class="card-body">
				<div class="row">
					<div class="col-md-6">
						<ul class="list-unstyled mb-0">
							<li><span class="badge bg-success"><i class="fas fa-check"></i></span> <?= __d('translate', 'All fields translated (manual)') ?></li>
							<li><span class="badge bg-info"><i class="fas fa-robot"></i></span> <?= __d('translate', 'All fields translated (auto)') ?></li>
							<li><span class="badge bg-warning text-dark"><i class="fas fa-exclamation"></i></span> <?= __d('translate', 'Partially translated') ?></li>
						</ul>
					</div>
					<div class="col-md-6">
						<ul class="list-unstyled mb-0">
							<li><span class="badge bg-danger"><i class="fas fa-times"></i></span> <?= __d('translate', 'Translation exists but empty') ?></li>
							<li><span class="badge bg-secondary"><i class="fas fa-minus"></i></span> <?= __d('translate', 'No translation record') ?></li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
