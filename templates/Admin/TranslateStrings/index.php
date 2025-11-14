<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\Translate\Model\Entity\TranslateString> $translateStrings
 * @var bool $_isSearch
 */

use Cake\Core\Plugin;

?>
<div class="row">
	<!-- Sidebar -->
	<nav class="col-lg-3 col-md-4 mb-4">
		<div class="card">
			<div class="card-header">
				<i class="fas fa-bars"></i> <?= __d('translate', 'Actions') ?>
			</div>
			<div class="list-group list-group-flush">
				<?= $this->Html->link(
					'<i class="fas fa-home"></i> ' . __d('translate', 'Overview'),
					['controller' => 'Translate', 'action' => 'index'],
					['escape' => false, 'class' => 'list-group-item list-group-item-action'],
				) ?>
				<?= $this->Html->link(
					'<i class="fas fa-plus-circle"></i> ' . __d('translate', 'New Translate String'),
					['action' => 'add'],
					['escape' => false, 'class' => 'list-group-item list-group-item-action'],
				) ?>
			</div>
		</div>
	</nav>

	<!-- Main Content -->
	<div class="col-lg-9 col-md-8">
		<div class="page-header mb-4">
			<h1><i class="fas fa-language"></i> <?= __d('translate', 'Translate Strings') ?></h1>
		</div>

		<!-- Filter Form -->
		<div class="card mb-4">
			<div class="card-header">
				<i class="fas fa-filter"></i> <?= __d('translate', 'Filter') ?>
			</div>
			<div class="card-body">
				<?php
				echo $this->Form->create(null, ['valueSources' => 'query', 'class' => 'row g-3']);
				?>
				<div class="col-md-4">
					<?= $this->Form->control('translate_domain_id', ['empty' => ' - ' . __d('translate', 'noLimitation') . ' - ', 'label' => '<i class="fas fa-folder"></i> ' . __d('translate', 'Domain'), 'escape' => false]) ?>
				</div>
				<div class="col-md-4">
					<?= $this->Form->control('search', ['placeholder' => __d('translate', 'Search...'), 'label' => '<i class="fas fa-search"></i> ' . __d('translate', 'Search'), 'escape' => false]) ?>
				</div>
				<div class="col-md-4">
					<label class="form-label">&nbsp;</label>
					<div class="form-check">
						<?= $this->Form->control('missing_translation', ['type' => 'checkbox', 'hiddenField' => '', 'label' => '<i class="fas fa-exclamation-triangle"></i> ' . __d('translate', 'Missing Translation'), 'escape' => false, 'class' => 'form-check-input']) ?>
					</div>
				</div>
				<div class="col-12">
					<div class="d-flex justify-content-between align-items-center">
						<small class="text-muted">
							<i class="fas fa-info-circle"></i>
							<?= __d('translate', 'Please note that name/context are case sensitive by default!') ?>
						</small>
						<div class="btn-group">
							<?= $this->Form->button(
								'<i class="fas fa-filter"></i> ' . __d('translate', 'Filter'),
								['type' => 'submit', 'class' => 'btn btn-primary', 'escape' => false],
							) ?>
							<?php if (!empty($_isSearch)) { ?>
								<?= $this->Html->link(
									'<i class="fas fa-times"></i> ' . __d('translate', 'Reset'),
									['action' => 'index'],
									['class' => 'btn btn-outline-secondary', 'escape' => false],
								) ?>
							<?php } ?>
						</div>
					</div>
				</div>
				<?php
				echo $this->Form->end();
				?>
			</div>
		</div>

		<!-- Results Table -->
		<div class="card">
			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-hover align-middle">
						<thead>
							<tr>
								<th><?= $this->Paginator->sort('name'); ?></th>
								<th class="text-center"><?= $this->Paginator->sort('active') ?></th>
								<th class="text-center"><?= $this->Paginator->sort('is_html', 'HTML') ?></th>
								<th class="text-center"><?= $this->Paginator->sort('plural') ?></th>
								<th class="text-center"><?= $this->Paginator->sort('context') ?></th>
								<th><?= $this->Paginator->sort('last_import', null, ['direction' => 'desc']) ?></th>
								<th><?= $this->Paginator->sort('created', null, ['direction' => 'desc']) ?></th>
								<th><?= $this->Paginator->sort('modified', null, ['direction' => 'desc']) ?></th>
								<th class="text-center"><?= __d('translate', 'Actions') ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($translateStrings as $translateString) : ?>
							<tr>
								<td>
									<span class="badge bg-dark me-2">
										<i class="fas fa-folder"></i>
										<?= h($translateString->translate_domain->name); ?>
									</span>
									<?= h($this->Text->truncate($translateString['name'])); ?>
								</td>
								<td class="text-center">
									<?= $this->element('Translate.yes_no', ['value' => $translateString->active]) ?>
								</td>
								<td class="text-center">
									<?= $this->element('Translate.yes_no', ['value' => $translateString->is_html]) ?>
								</td>
								<td class="text-center">
									<?= $this->element('Translate.yes_no', ['value' => $translateString->plural, 'title' => $translateString->plural]) ?>
								</td>
								<td class="text-center">
									<?= $this->element('Translate.yes_no', ['value' => $translateString->context, 'title' => $translateString->context]) ?>
								</td>
								<td><small><?= $this->Time->nice($translateString->last_import) ?></small></td>
								<td><small><?= $this->Time->nice($translateString->created) ?></small></td>
								<td><small><?= $this->Time->nice($translateString->modified) ?></small></td>
								<td class="text-center">
									<div class="btn-group btn-group-sm" role="group">
										<?= $this->Html->link(
											$this->Icon->render('translate'),
											['action' => 'translate', $translateString['id']],
											['escape' => false, 'class' => 'btn btn-outline-primary', 'title' => __d('translate', 'Translate'), 'data-bs-toggle' => 'tooltip'],
										); ?>
										<?= $this->Html->link(
											$this->Icon->render('view'),
											['action' => 'view', $translateString->id],
											['escape' => false, 'class' => 'btn btn-outline-info', 'title' => __d('translate', 'View'), 'data-bs-toggle' => 'tooltip'],
										); ?>
										<?= $this->Html->link(
											$this->Icon->render('edit'),
											['action' => 'edit', $translateString->id],
											['escape' => false, 'class' => 'btn btn-outline-secondary', 'title' => __d('translate', 'Edit'), 'data-bs-toggle' => 'tooltip'],
										); ?>
										<?= $this->Form->postLink(
											$this->Icon->render('delete'),
											['action' => 'delete', $translateString->id],
											['escape' => false, 'class' => 'btn btn-outline-danger', 'confirm' => __d('translate', 'Are you sure you want to delete # {0}?', $translateString->id), 'title' => __d('translate', 'Delete'), 'data-bs-toggle' => 'tooltip'],
										); ?>
									</div>
								</td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
			<div class="card-footer">
				<?php
				if (Plugin::isLoaded('Tools')) {
					echo $this->element('Tools.pagination');
				} else {
					echo $this->element('pagination');
				}
				?>
			</div>
		</div>
	</div>
</div>
