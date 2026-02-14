<?php
/**
 * @var \App\View\AppView $this
 * @var string $tableName
 * @var string $baseTableName
 * @var iterable $entries
 * @var string $strategy
 * @var array<string> $translatedFields
 * @var array<string> $locales
 * @var bool $hasAutoField
 */

$this->Html->script('https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js', ['block' => true]);
$this->Html->css('https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css', ['block' => true]);
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
				<span class="badge bg-<?= $strategy === 'eav' ? 'primary' : 'secondary' ?>">
					<?= h(strtoupper($strategy)) ?>
				</span>
			</div>
			<div class="card-body">
				<!-- Filters -->
				<?= $this->Form->create(null, ['type' => 'get', 'class' => 'row g-3']) ?>
					<div class="col-md-3">
						<?= $this->Form->control('locale', [
							'type' => 'select',
							'options' => array_combine($locales, $locales),
							'empty' => __d('translate', '-- All Locales --'),
							'value' => $this->request->getQuery('locale'),
							'label' => __d('translate', 'Locale'),
							'class' => 'form-select',
						]) ?>
					</div>

					<?php if ($strategy === 'eav') { ?>
						<div class="col-md-3">
							<?= $this->Form->control('field', [
								'type' => 'select',
								'options' => array_combine($translatedFields, $translatedFields),
								'empty' => __d('translate', '-- All Fields --'),
								'value' => $this->request->getQuery('field'),
								'label' => __d('translate', 'Field'),
								'class' => 'form-select',
							]) ?>
						</div>
					<?php } ?>

					<?php if ($hasAutoField) { ?>
						<div class="col-md-2">
							<?= $this->Form->control('auto', [
								'type' => 'select',
								'options' => [
									'' => __d('translate', '-- All --'),
									'1' => __d('translate', 'Auto'),
									'0' => __d('translate', 'Manual'),
								],
								'value' => $this->request->getQuery('auto'),
								'label' => __d('translate', 'Type'),
								'class' => 'form-select',
							]) ?>
						</div>
					<?php } ?>

					<div class="col-md-3">
						<?= $this->Form->control('search', [
							'type' => 'text',
							'value' => $this->request->getQuery('search'),
							'label' => __d('translate', 'Search'),
							'placeholder' => __d('translate', 'Search content...'),
							'class' => 'form-control',
						]) ?>
					</div>

					<div class="col-md-1 d-flex align-items-end">
						<?= $this->Form->button('<i class="fas fa-filter"></i>', [
							'type' => 'submit',
							'class' => 'btn btn-primary',
							'escape' => false,
							'title' => __d('translate', 'Filter'),
						]) ?>
					</div>
				<?= $this->Form->end() ?>
			</div>
		</div>
	</div>
</div>

<!-- Batch Actions -->
<?= $this->Form->create(null, ['url' => ['action' => 'autoTranslate', $tableName], 'id' => 'batch-form']) ?>
<div class="row mb-3">
	<div class="col-12">
		<div class="btn-toolbar justify-content-between">
			<div class="btn-group">
				<button type="button" class="btn btn-outline-secondary" id="select-all">
					<i class="fas fa-check-square"></i> <?= __d('translate', 'Select All') ?>
				</button>
				<button type="button" class="btn btn-outline-secondary" id="select-none">
					<i class="fas fa-square"></i> <?= __d('translate', 'Select None') ?>
				</button>
			</div>

			<div class="btn-group">
				<?php if ($hasAutoField) { ?>
					<button type="submit" formaction="<?= $this->Url->build(['action' => 'batchUpdateAuto', $tableName]) ?>"
							name="auto" value="0" class="btn btn-outline-warning">
						<i class="fas fa-user"></i> <?= __d('translate', 'Mark as Manual') ?>
					</button>
					<button type="submit" formaction="<?= $this->Url->build(['action' => 'batchUpdateAuto', $tableName]) ?>"
							name="auto" value="1" class="btn btn-outline-info">
						<i class="fas fa-robot"></i> <?= __d('translate', 'Mark as Auto') ?>
					</button>
				<?php } ?>
				<button type="submit" class="btn btn-primary">
					<i class="fas fa-language"></i> <?= __d('translate', 'Auto-Translate Selected') ?>
				</button>
			</div>
		</div>
	</div>
</div>

<!-- Entries Table -->
<div class="row">
	<div class="col-12">
		<div class="card">
			<div class="card-body p-0">
				<div class="table-responsive">
					<table class="table table-striped table-hover mb-0">
						<thead class="table-light">
							<tr>
								<th style="width: 40px;">
									<input type="checkbox" id="check-all" class="form-check-input">
								</th>
								<th><?= __d('translate', 'ID') ?></th>
								<th><?= __d('translate', 'Locale') ?></th>
								<?php if ($strategy === 'eav') { ?>
									<th><?= __d('translate', 'Foreign Key') ?></th>
									<th><?= __d('translate', 'Field') ?></th>
									<th><?= __d('translate', 'Content') ?></th>
								<?php } else { ?>
									<th><?= __d('translate', 'Foreign Key') ?></th>
									<?php foreach ($translatedFields as $field) { ?>
										<th><?= h(ucfirst($field)) ?></th>
									<?php } ?>
								<?php } ?>
								<?php if ($hasAutoField) { ?>
									<th><?= __d('translate', 'Auto') ?></th>
								<?php } ?>
								<th class="actions"><?= __d('translate', 'Actions') ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($entries as $entry) { ?>
								<tr>
									<td>
										<input type="checkbox" name="entry_ids[]"
											   value="<?= h($entry->id) ?>" class="form-check-input entry-checkbox">
									</td>
									<td><?= h($entry->id) ?></td>
									<td>
										<span class="badge bg-primary"><?= h($entry->locale) ?></span>
									</td>
									<?php if ($strategy === 'eav') { ?>
										<td><?= h($entry->foreign_key) ?></td>
										<td>
											<span class="badge bg-info"><?= h($entry->field) ?></span>
										</td>
										<td>
											<?php
											$content = $entry->content ?? '';
											if (strlen($content) > 100) {
												echo h(substr($content, 0, 97)) . '...';
											} else {
												echo h($content);
											}
											?>
										</td>
									<?php } else { ?>
										<td><?= h($entry->foreign_key) ?></td>
										<?php foreach ($translatedFields as $field) { ?>
											<td>
												<?php
												$value = $entry->$field ?? '';
												if (strlen($value) > 80) {
													echo h(substr($value, 0, 77)) . '...';
												} else {
													echo h($value);
												}
												?>
											</td>
										<?php } ?>
									<?php } ?>
									<?php if ($hasAutoField) { ?>
										<td>
											<?php if ($entry->auto) { ?>
												<span class="badge bg-info" title="<?= __d('translate', 'Auto-translated') ?>">
													<i class="fas fa-robot"></i>
												</span>
											<?php } else { ?>
												<span class="badge bg-secondary" title="<?= __d('translate', 'Manual') ?>">
													<i class="fas fa-user"></i>
												</span>
											<?php } ?>
										</td>
									<?php } ?>
									<td class="actions">
										<?= $this->Html->link(
											'<i class="fas fa-eye"></i>',
											['action' => 'view', $tableName, $entry->id],
											['class' => 'btn btn-sm btn-outline-primary', 'escape' => false, 'title' => __d('translate', 'View')],
										) ?>
										<?= $this->Html->link(
											'<i class="fas fa-edit"></i>',
											['action' => 'edit', $tableName, $entry->id],
											['class' => 'btn btn-sm btn-outline-secondary', 'escape' => false, 'title' => __d('translate', 'Edit')],
										) ?>
										<?= $this->Form->postLink(
											'<i class="fas fa-trash"></i>',
											['action' => 'delete', $tableName, $entry->id],
											[
												'class' => 'btn btn-sm btn-outline-danger',
												'escape' => false,
												'title' => __d('translate', 'Delete'),
												'confirm' => __d('translate', 'Are you sure you want to delete this entry?'),
												'block' => true,
											],
										) ?>
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
	</div>
</div>
<?= $this->Form->end() ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
	const checkAll = document.getElementById('check-all');
	const selectAll = document.getElementById('select-all');
	const selectNone = document.getElementById('select-none');
	const checkboxes = document.querySelectorAll('.entry-checkbox');

	if (checkAll) {
		checkAll.addEventListener('change', function() {
			checkboxes.forEach(cb => cb.checked = this.checked);
		});
	}

	if (selectAll) {
		selectAll.addEventListener('click', function() {
			checkboxes.forEach(cb => cb.checked = true);
			if (checkAll) checkAll.checked = true;
		});
	}

	if (selectNone) {
		selectNone.addEventListener('click', function() {
			checkboxes.forEach(cb => cb.checked = false);
			if (checkAll) checkAll.checked = false;
		});
	}
});
</script>
