<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\Translate\Model\Entity\TranslateString> $translateStrings
 * @var array $translateDomains
 * @var array $translateLocales
 */
?>

<div class="page-header">
	<h1>
		<i class="fas fa-language"></i>
		<?= __d('translate', 'Translation Terms') ?>
	</h1>
</div>

<!-- Filters -->
<div class="card mb-4">
	<div class="card-header">
		<i class="fas fa-filter"></i>
		<?= __d('translate', 'Filters') ?>
	</div>
	<div class="card-body">
		<?= $this->Form->create(null, ['type' => 'get', 'valueSources' => 'query']) ?>
		<div class="row g-3">
			<div class="col-md-3">
				<?= $this->Form->control('search', [
					'label' => __d('translate', 'Search'),
					'placeholder' => __d('translate', 'Search in strings...'),
					'class' => 'form-control',
				]) ?>
			</div>
			<div class="col-md-3">
				<?= $this->Form->control('translate_domain_id', [
					'label' => __d('translate', 'Domain'),
					'options' => $translateDomains,
					'empty' => __d('translate', 'All domains'),
					'class' => 'form-select',
				]) ?>
			</div>
			<div class="col-md-2">
				<?= $this->Form->control('skipped', [
					'label' => __d('translate', 'Skipped'),
					'options' => [
						1 => __d('translate', 'Yes'),
						0 => __d('translate', 'No'),
					],
					'empty' => __d('translate', 'All'),
					'class' => 'form-select',
				]) ?>
			</div>
			<div class="col-md-2">
				<?= $this->Form->control('is_html', [
					'label' => __d('translate', 'HTML'),
					'options' => [
						1 => __d('translate', 'Yes'),
						0 => __d('translate', 'No'),
					],
					'empty' => __d('translate', 'All'),
					'class' => 'form-select',
				]) ?>
			</div>
			<div class="col-md-2">
				<?= $this->Form->control('has_plural', [
					'label' => __d('translate', 'Has Plural'),
					'options' => [
						1 => __d('translate', 'Yes'),
						0 => __d('translate', 'No'),
					],
					'empty' => __d('translate', 'All'),
					'class' => 'form-select',
				]) ?>
			</div>
		</div>
		<div class="row g-3 mt-2">
			<div class="col-md-3">
				<?= $this->Form->control('missing_translation', [
					'label' => __d('translate', 'Missing Translation'),
					'type' => 'checkbox',
					'class' => 'form-check-input',
				]) ?>
			</div>
			<div class="col-md-9 text-end">
				<?= $this->Form->button(__d('translate', 'Filter'), [
					'type' => 'submit',
					'class' => 'btn btn-primary',
				]) ?>
				<?= $this->Html->link(__d('translate', 'Reset'), ['action' => 'terms'], [
					'class' => 'btn btn-secondary',
				]) ?>
			</div>
		</div>
		<?= $this->Form->end() ?>
	</div>
</div>

<!-- Results -->
<div class="card">
	<div class="card-header d-flex justify-content-between align-items-center">
		<span>
			<i class="fas fa-list"></i>
			<?= __d('translate', 'Translation Strings') ?>
		</span>
		<span class="badge bg-primary"><?= $this->Paginator->counter(__d('translate', '{{count}} strings')) ?></span>
	</div>
	<div class="table-responsive">
		<table class="table table-hover mb-0">
			<thead>
				<tr>
					<th><?= $this->Paginator->sort('id', __d('translate', 'ID')) ?></th>
					<th><?= $this->Paginator->sort('TranslateDomains.name', __d('translate', 'Domain')) ?></th>
					<th><?= $this->Paginator->sort('name', __d('translate', 'String')) ?></th>
					<th><?= __d('translate', 'Translations') ?></th>
					<th><?= $this->Paginator->sort('skipped', __d('translate', 'Skipped')) ?></th>
					<th class="actions"><?= __d('translate', 'Actions') ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($translateStrings as $translateString) { ?>
					<tr>
						<td><?= $this->Number->format($translateString->id) ?></td>
						<td>
							<span class="badge bg-info">
								<?= h($translateString->translate_domain->name) ?>
							</span>
						</td>
						<td>
							<div>
								<strong><?= h($translateString->name) ?></strong>
								<?php if ($translateString->plural) { ?>
									<br>
									<small class="text-muted">
										<i class="fas fa-angle-right"></i>
										<?= h($translateString->plural) ?>
									</small>
								<?php } ?>
								<?php if ($translateString->context) { ?>
									<br>
									<small class="text-muted">
										<i class="fas fa-tag"></i>
										<?= h($translateString->context) ?>
									</small>
								<?php } ?>
							</div>
							<?php if ($translateString->is_html) { ?>
								<span class="badge bg-warning text-dark">HTML</span>
							<?php } ?>
						</td>
						<td>
							<?php if ($translateString->translate_terms) { ?>
								<div class="d-flex flex-wrap gap-1">
									<?php foreach ($translateString->translate_terms as $term) { ?>
										<?php if ($term->content) { ?>
											<span class="badge bg-success" title="<?= h($term->content) ?>">
												<?= h($term->translate_locale->locale) ?>
											</span>
										<?php } else { ?>
											<span class="badge bg-secondary">
												<?= h($term->translate_locale->locale) ?>
											</span>
										<?php } ?>
									<?php } ?>
								</div>
							<?php } else { ?>
								<span class="badge bg-danger"><?= __d('translate', 'None') ?></span>
							<?php } ?>
						</td>
						<td>
							<?php if ($translateString->skipped) { ?>
								<span class="badge bg-warning"><i class="fas fa-check"></i></span>
							<?php } else { ?>
								<span class="badge bg-light text-dark">-</span>
							<?php } ?>
						</td>
						<td class="actions">
							<?= $this->Html->link(
								'<i class="fas fa-language"></i>',
								['action' => 'translate', $translateString->translate_domain->name, $translateString->id],
								['escape' => false, 'class' => 'btn btn-sm btn-primary', 'title' => __d('translate', 'Translate')]
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
