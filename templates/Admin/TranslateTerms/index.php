<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\Translate\Model\Entity\TranslateTerm> $translateTerms
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
					'<i class="fas fa-language"></i> ' . __d('translate', 'List Translate Strings'),
					['controller' => 'TranslateStrings', 'action' => 'index'],
					['escape' => false, 'class' => 'list-group-item list-group-item-action'],
				) ?>
				<?= $this->Html->link(
					'<i class="fas fa-plus-circle"></i> ' . __d('translate', 'New Translate String'),
					['controller' => 'TranslateStrings', 'action' => 'add'],
					['escape' => false, 'class' => 'list-group-item list-group-item-action'],
				) ?>
				<?= $this->Html->link(
					'<i class="fas fa-globe"></i> ' . __d('translate', 'List Locales'),
					['controller' => 'TranslateLocales', 'action' => 'index'],
					['escape' => false, 'class' => 'list-group-item list-group-item-action'],
				) ?>
			</div>
		</div>
	</nav>

	<!-- Main Content -->
	<div class="col-lg-9 col-md-8">
		<div class="page-header mb-4">
			<h1><i class="fas fa-comments"></i> <?= __d('translate', 'Translate Terms') ?></h1>
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
				<div class="col-md-6">
					<?= $this->Form->control('translate_locale_id', ['empty' => ' - ' . __d('translate', 'noLimitation') . ' - ', 'label' => '<i class="fas fa-globe"></i> ' . __d('translate', 'Locale'), 'escape' => false]) ?>
				</div>
				<div class="col-md-6">
					<?= $this->Form->control('search', ['placeholder' => __d('translate', 'Search...'), 'label' => '<i class="fas fa-search"></i> ' . __d('translate', 'Search'), 'escape' => false]) ?>
				</div>
				<div class="col-12">
					<div class="d-flex justify-content-end">
						<div class="btn-group">
							<?= $this->Form->button(
								'<i class="fas fa-filter"></i> ' . __d('translate', 'Filter'),
								['type' => 'submit', 'class' => 'btn btn-primary', 'escapeTitle' => false],
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
				<?php echo $this->Form->end(); ?>
			</div>
		</div>

		<!-- Results Table -->
		<div class="card">
			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-hover">
						<thead>
							<tr>
								<th><?= $this->Paginator->sort('translate_string_id', __d('translate', 'Text')) ?></th>
								<th><?= $this->Paginator->sort('content', __d('translate', 'Translation')) ?></th>
								<th><?= $this->Paginator->sort('translate_locale_id', __d('translate', 'Locale')) ?></th>
								<th class="text-center"><?= $this->Paginator->sort('confirmed', __d('translate', 'Confirmed')) ?></th>
								<th><?= $this->Paginator->sort('created', null, ['direction' => 'desc']) ?></th>
								<th><?= $this->Paginator->sort('modified', null, ['direction' => 'desc']) ?></th>
								<th class="actions text-center"><?= __d('translate', 'Actions') ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($translateTerms as $translateTerm) : ?>
							<tr>
								<td>
									<?php if ($translateTerm->has('translate_string')) { ?>
										<?= $this->Html->link(
											$this->Text->truncate($translateTerm->translate_string->name, 60),
											['controller' => 'TranslateStrings', 'action' => 'view', $translateTerm->translate_string->id],
										) ?>
									<?php } ?>

									<?php if ($translateTerm->comment) { ?>
										<span class="ms-1" title="<?= h($translateTerm->comment) ?>">
											<?= $this->Icon->render('comment') ?>
										</span>
									<?php } ?>
								</td>
								<td>
									<?= $this->Text->truncate($translateTerm->content, 60) ?>
								</td>
								<td>
									<?php if ($translateTerm->has('translate_locale')) { ?>
										<?= $this->Html->link(
											$translateTerm->translate_locale->name,
											['controller' => 'TranslateLocales', 'action' => 'view', $translateTerm->translate_locale->id],
										) ?>
									<?php } ?>
								</td>
								<td class="text-center">
									<?= $this->element('Translate.yes_no', ['value' => $translateTerm->confirmed]) ?>
									<?php if ($translateTerm->confirmed_by) { ?>
										<div>
											<small class="text-muted"><?= h($translateTerm->confirmed_by) ?></small>
										</div>
									<?php } ?>
								</td>
								<td>
									<small><?= $this->Time->nice($translateTerm->created) ?></small>
								</td>
								<td>
									<small><?= $this->Time->nice($translateTerm->modified) ?></small>
								</td>
								<td class="actions text-center">
									<div class="btn-group btn-group-sm" role="group">
										<?= $this->Html->link(
											$this->Icon->render('view'),
											['action' => 'view', $translateTerm->id],
											['escape' => false, 'class' => 'btn btn-outline-primary', 'title' => __d('translate', 'View')],
										) ?>
										<?= $this->Html->link(
											$this->Icon->render('edit'),
											['action' => 'edit', $translateTerm->id],
											['escape' => false, 'class' => 'btn btn-outline-secondary', 'title' => __d('translate', 'Edit')],
										) ?>
										<?= $this->Form->postLink(
											$this->Icon->render('delete'),
											['action' => 'delete', $translateTerm->id],
											['escape' => false, 'confirm' => __d('translate', 'Are you sure you want to delete # {0}?', $translateTerm->id), 'class' => 'btn btn-outline-danger', 'title' => __d('translate', 'Delete')],
										) ?>
									</div>
								</td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>

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
