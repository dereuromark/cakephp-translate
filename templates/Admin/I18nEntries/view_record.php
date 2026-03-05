<?php
/**
 * @var \App\View\AppView $this
 * @var string $tableName
 * @var string $baseTableName
 * @var \Cake\ORM\Entity $baseRecord
 * @var array $translations
 * @var array<string, \Cake\ORM\Entity> $translationsByLocale
 * @var array<string> $locales
 * @var array<string> $translatedFields
 * @var bool $hasAutoField
 * @var string|null $displayField
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
		<li class="breadcrumb-item active">
			<?= __d('translate', 'Record #{0}', $baseRecord->id) ?>
		</li>
	</ol>
</nav>

<div class="row mb-4">
	<div class="col-12">
		<div class="card">
			<div class="card-header bg-primary text-white">
				<h5 class="mb-0">
					<i class="fas fa-database"></i>
					<?= h($baseTableName) ?> #<?= h($baseRecord->id) ?>
					<?php if ($displayField && $baseRecord->$displayField) { ?>
						- <?= h($baseRecord->$displayField) ?>
					<?php } ?>
				</h5>
			</div>
			<div class="card-body">
				<h6><?= __d('translate', 'Base Record Fields') ?></h6>
				<dl class="row mb-0">
					<dt class="col-sm-2"><?= __d('translate', 'ID') ?></dt>
					<dd class="col-sm-10"><?= h($baseRecord->id) ?></dd>

					<?php foreach ($translatedFields as $field) { ?>
						<dt class="col-sm-2"><?= h(ucfirst($field)) ?></dt>
						<dd class="col-sm-10">
							<?php
							$value = $baseRecord->$field ?? '';
							if (strlen($value) > 200) {
								echo '<div class="text-truncate" style="max-width: 600px;">' . h(substr($value, 0, 200)) . '...</div>';
							} else {
								echo h($value) ?: '<span class="text-muted">(' . __d('translate', 'empty') . ')</span>';
							}
							?>
						</dd>
					<?php } ?>
				</dl>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-12">
		<div class="card">
			<div class="card-header">
				<h5 class="mb-0">
					<i class="fas fa-language"></i> <?= __d('translate', 'Translations') ?>
				</h5>
			</div>
			<div class="card-body p-0">
				<table class="table table-striped mb-0">
					<thead class="table-light">
						<tr>
							<th><?= __d('translate', 'Locale') ?></th>
							<?php foreach ($translatedFields as $field) { ?>
								<th><?= h(ucfirst($field)) ?></th>
							<?php } ?>
							<?php if ($hasAutoField) { ?>
								<th><?= __d('translate', 'Type') ?></th>
							<?php } ?>
							<th class="actions"><?= __d('translate', 'Actions') ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($locales as $locale) { ?>
							<?php
							$translation = $translationsByLocale[$locale] ?? null;
							$hasTranslation = $translation !== null;
							?>
							<tr class="<?= $hasTranslation ? '' : 'table-warning' ?>">
								<td>
									<span class="badge bg-<?= $hasTranslation ? 'primary' : 'secondary' ?>">
										<?= h($locale) ?>
									</span>
								</td>
								<?php foreach ($translatedFields as $field) { ?>
									<td>
										<?php if ($hasTranslation) { ?>
											<?php
											$value = $translation->$field ?? '';
											if (strlen($value) > 80) {
												echo h(substr($value, 0, 77)) . '...';
											} else {
												echo h($value) ?: '<span class="text-muted">(' . __d('translate', 'empty') . ')</span>';
											}
											?>
										<?php } else { ?>
											<span class="text-muted">
												<i class="fas fa-minus"></i> <?= __d('translate', 'Not translated') ?>
											</span>
										<?php } ?>
									</td>
								<?php } ?>
								<?php if ($hasAutoField) { ?>
									<td>
										<?php if ($hasTranslation) { ?>
											<?php if ($translation->auto) { ?>
												<span class="badge bg-info" title="<?= __d('translate', 'Auto-translated') ?>">
													<i class="fas fa-robot"></i> <?= __d('translate', 'Auto') ?>
												</span>
											<?php } else { ?>
												<span class="badge bg-secondary" title="<?= __d('translate', 'Manual') ?>">
													<i class="fas fa-user"></i> <?= __d('translate', 'Manual') ?>
												</span>
											<?php } ?>
										<?php } else { ?>
											<span class="text-muted">-</span>
										<?php } ?>
									</td>
								<?php } ?>
								<td class="actions">
									<?php if ($hasTranslation) { ?>
										<?= $this->Html->link(
											'<i class="fas fa-edit"></i> ' . __d('translate', 'Edit'),
											['action' => 'editTranslation', $tableName, $baseRecord->id, $locale],
											['class' => 'btn btn-sm btn-outline-primary', 'escape' => false],
										) ?>
									<?php } else { ?>
										<?= $this->Html->link(
											'<i class="fas fa-plus"></i> ' . __d('translate', 'Add'),
											['action' => 'addTranslation', $tableName, $baseRecord->id, $locale],
											['class' => 'btn btn-sm btn-success', 'escape' => false],
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

<div class="row mt-4">
	<div class="col-12">
		<?= $this->Html->link(
			'<i class="fas fa-arrow-left"></i> ' . __d('translate', 'Back to List'),
			['action' => 'entries', $tableName],
			['class' => 'btn btn-secondary', 'escape' => false],
		) ?>
	</div>
</div>
