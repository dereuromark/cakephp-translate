<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\Translate\Model\Entity\TranslateTerm> $pendingTerms
 * @var array $localeStats
 */
?>

<div class="page-header">
	<h1>
		<i class="fas fa-clock"></i>
		<?= __d('translate', 'Pending Translations') ?>
	</h1>
</div>

<!-- Locale Statistics -->
<div class="row mb-4">
	<?php foreach ($localeStats as $localeId => $stats) { ?>
		<?php if ($stats['count'] > 0) { ?>
			<div class="col-md-3">
				<div class="card border-warning">
					<div class="card-body">
						<h5 class="card-title">
							<i class="fas fa-flag"></i>
							<?= h($stats['name']) ?>
							<small class="text-muted">(<?= h($stats['locale']) ?>)</small>
						</h5>
						<h3 class="text-warning"><?= $stats['count'] ?></h3>
						<p class="text-muted mb-3"><?= __d('translate', 'pending translations') ?></p>
						<?= $this->Form->create(null, ['url' => ['action' => 'batchConfirm']]) ?>
						<?= $this->Form->hidden('locale_id', ['value' => $localeId]) ?>
						<?= $this->Form->button(
							'<i class="fas fa-check"></i> ' . __d('translate', 'Confirm All'),
							['type' => 'submit', 'class' => 'btn btn-sm btn-success w-100', 'escapeTitle' => false]
						) ?>
						<?= $this->Form->end() ?>
					</div>
				</div>
			</div>
		<?php } ?>
	<?php } ?>

	<?php if (array_sum(array_column($localeStats, 'count')) > 0) { ?>
		<div class="col-md-3">
			<div class="card border-primary">
				<div class="card-body">
					<h5 class="card-title">
						<i class="fas fa-globe"></i>
						<?= __d('translate', 'All Locales') ?>
					</h5>
					<h3 class="text-primary"><?= array_sum(array_column($localeStats, 'count')) ?></h3>
					<p class="text-muted mb-3"><?= __d('translate', 'total pending') ?></p>
					<?= $this->Form->create(null, ['url' => ['action' => 'batchConfirm']]) ?>
					<?= $this->Form->hidden('confirm_all', ['value' => 1]) ?>
					<?= $this->Form->button(
						'<i class="fas fa-check-double"></i> ' . __d('translate', 'Confirm All'),
						[
							'type' => 'submit',
							'class' => 'btn btn-sm btn-primary w-100',
							'escapeTitle' => false,
							'confirm' => __d('translate', 'Are you sure you want to confirm all pending translations?'),
						]
					) ?>
					<?= $this->Form->end() ?>
				</div>
			</div>
		</div>
	<?php } ?>
</div>

<!-- Pending Translations List -->
<div class="card">
	<div class="card-header d-flex justify-content-between align-items-center">
		<span>
			<i class="fas fa-list"></i>
			<?= __d('translate', 'Pending Translations List') ?>
		</span>
		<span class="badge bg-warning"><?= $this->Paginator->counter(__d('translate', '{{count}} pending')) ?></span>
	</div>
	<div class="table-responsive">
		<table class="table table-hover mb-0">
			<thead>
				<tr>
					<th><?= $this->Paginator->sort('id', __d('translate', 'ID')) ?></th>
					<th><?= $this->Paginator->sort('TranslateLocales.name', __d('translate', 'Locale')) ?></th>
					<th><?= $this->Paginator->sort('TranslateDomains.name', __d('translate', 'Domain')) ?></th>
					<th><?= __d('translate', 'String') ?></th>
					<th><?= __d('translate', 'Translation') ?></th>
					<th><?= $this->Paginator->sort('modified', __d('translate', 'Modified')) ?></th>
					<th class="actions"><?= __d('translate', 'Actions') ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if (count($pendingTerms) === 0) { ?>
					<tr>
						<td colspan="7" class="text-center text-muted py-4">
							<i class="fas fa-check-circle fa-3x mb-3"></i>
							<p><?= __d('translate', 'No pending translations!') ?></p>
						</td>
					</tr>
				<?php } ?>
				<?php foreach ($pendingTerms as $term) { ?>
					<tr>
						<td><?= $this->Number->format($term->id) ?></td>
						<td>
							<span class="badge bg-info">
								<?= h($term->translate_locale->locale) ?>
							</span>
						</td>
						<td>
							<span class="badge bg-primary">
								<?= h($term->translate_string->translate_domain->name) ?>
							</span>
						</td>
						<td>
							<strong><?= h($term->translate_string->name) ?></strong>
							<?php if ($term->translate_string->plural) { ?>
								<br>
								<small class="text-muted">
									<i class="fas fa-angle-right"></i>
									<?= h($term->translate_string->plural) ?>
								</small>
							<?php } ?>
						</td>
						<td>
							<?= h($term->content) ?>
							<?php if ($term->plural_2) { ?>
								<br>
								<small class="text-muted">
									<i class="fas fa-angle-right"></i>
									<?= h($term->plural_2) ?>
								</small>
							<?php } ?>
						</td>
						<td>
							<?php if ($term->modified) { ?>
								<?= $this->Time->timeAgoInWords($term->modified) ?>
							<?php } ?>
						</td>
						<td class="actions">
							<?= $this->Html->link(
								'<i class="fas fa-eye"></i>',
								['action' => 'view', $term->id],
								['escape' => false, 'class' => 'btn btn-sm btn-info', 'title' => __d('translate', 'View')]
							) ?>
							<?= $this->Form->postLink(
								'<i class="fas fa-check"></i>',
								['action' => 'confirm', $term->id],
								[
									'escapeTitle' => false,
									'class' => 'btn btn-sm btn-success',
									'title' => __d('translate', 'Confirm'),
								]
							) ?>
						</td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div class="card-footer">
		<div class="paginator">
			<ul class="pagination mb-0">
				<?= $this->Paginator->first('<< ' . __d('translate', 'first')) ?>
				<?= $this->Paginator->prev('< ' . __d('translate', 'previous')) ?>
				<?= $this->Paginator->numbers() ?>
				<?= $this->Paginator->next(__d('translate', 'next') . ' >') ?>
				<?= $this->Paginator->last(__d('translate', 'last') . ' >>') ?>
			</ul>
			<p class="text-muted mt-2 mb-0">
				<?= $this->Paginator->counter(__d('translate', 'Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?>
			</p>
		</div>
	</div>
</div>
