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
 * @var string $sourceLocale
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
			<div class="card-header d-flex justify-content-between align-items-center">
				<h5 class="mb-0">
					<i class="fas fa-language"></i> <?= __d('translate', 'Translations') ?>
				</h5>
				<?= $this->Form->postLink(
					'<i class="fas fa-magic"></i> ' . __d('translate', 'Auto-translate All'),
					['action' => 'autoTranslateRecord', $tableName, $baseRecord->id],
					[
						'class' => 'btn btn-info btn-sm',
						'escape' => false,
						'confirm' => __d('translate', 'Auto-translate this record to all configured locales?'),
					],
				) ?>
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
							$isSourceLocale = ($locale === $sourceLocale);

							// Check if fully translated (all fields have content)
							$isFullyTranslated = false;
							if ($hasTranslation) {
								$isFullyTranslated = true;
								foreach ($translatedFields as $field) {
									if (empty($translation->$field)) {
										$isFullyTranslated = false;
										break;
									}
								}
							}

							// Determine row class
							$rowClass = '';
							if ($isSourceLocale) {
								$rowClass = 'table-light text-muted';
							} elseif (!$hasTranslation) {
								$rowClass = 'table-warning';
							}
							?>
							<tr class="<?= $rowClass ?>">
								<td>
									<span class="badge bg-<?= $isSourceLocale ? 'dark' : ($hasTranslation ? 'primary' : 'secondary') ?>">
										<?= h($locale) ?>
									</span>
									<?php if ($isSourceLocale) { ?>
										<small class="text-muted">(<?= __d('translate', 'source') ?>)</small>
									<?php } ?>
								</td>
								<?php foreach ($translatedFields as $field) { ?>
									<td>
										<?php if ($isSourceLocale) { ?>
											<span class="text-muted">
												<?= __d('translate', 'Uses base record') ?>
											</span>
										<?php } elseif ($hasTranslation) { ?>
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
										<?php if ($isSourceLocale) { ?>
											<span class="text-muted">-</span>
										<?php } elseif ($hasTranslation) { ?>
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
									<?php if ($isSourceLocale) { ?>
										<span class="text-muted">-</span>
									<?php } else { ?>
										<?php if ($hasTranslation) { ?>
											<?= $this->Html->link(
												'<i class="fas fa-edit"></i>',
												['action' => 'editTranslation', $tableName, $baseRecord->id, $locale],
												['class' => 'btn btn-sm btn-outline-primary', 'escape' => false, 'title' => __d('translate', 'Edit')],
											) ?>
										<?php } else { ?>
											<?= $this->Html->link(
												'<i class="fas fa-plus"></i>',
												['action' => 'addTranslation', $tableName, $baseRecord->id, $locale],
												['class' => 'btn btn-sm btn-success', 'escape' => false, 'title' => __d('translate', 'Add')],
											) ?>
										<?php } ?>
										<?php if (!$isFullyTranslated) { ?>
											<?= $this->Form->postLink(
												'<i class="fas fa-magic"></i>',
												['action' => 'autoTranslateRecord', $tableName, $baseRecord->id, '?' => ['locales' => [$locale]]],
												[
													'class' => 'btn btn-sm btn-outline-info',
													'escape' => false,
													'title' => __d('translate', 'Auto-translate'),
													'confirm' => __d('translate', 'Auto-translate to {0}?', $locale),
													'block' => true,
												],
											) ?>
										<?php } ?>
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
