<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\Translate\Model\Entity\TranslateDomain> $translateDomains
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
					'<i class="fas fa-plus-circle"></i> ' . __d('translate', 'New Translate Domain'),
					['action' => 'add'],
					['escape' => false, 'class' => 'list-group-item list-group-item-action'],
				) ?>
			</div>
		</div>
	</nav>

	<!-- Main Content -->
	<div class="col-lg-9 col-md-8">
		<div class="page-header mb-4">
			<h1><i class="fas fa-folder"></i> <?= __d('translate', 'Translate Domains') ?></h1>
		</div>

		<!-- Results Table -->
		<div class="card">
			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-hover">
						<thead>
							<tr>
								<th><?= $this->Paginator->sort('name') ?></th>
								<th class="text-center"><?= $this->Paginator->sort('active') ?></th>
								<th><?= $this->Paginator->sort('created', null, ['direction' => 'desc']) ?></th>
								<th><?= $this->Paginator->sort('modified', null, ['direction' => 'desc']) ?></th>
								<th class="actions text-center"><?= __d('translate', 'Actions') ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($translateDomains as $translateDomain) { ?>
							<tr>
								<td>
									<strong><?= h($translateDomain->name) ?></strong>
								</td>
								<td class="text-center">
									<?= $this->element('Translate.yes_no', ['value' => $translateDomain->active]) ?>
								</td>
								<td>
									<small><?= $this->Time->nice($translateDomain->created) ?></small>
								</td>
								<td>
									<small><?= $this->Time->nice($translateDomain->modified) ?></small>
								</td>
								<td class="actions text-center">
									<div class="btn-group btn-group-sm" role="group">
										<?= $this->Html->link(
											$this->Icon->render('view'),
											['action' => 'view', $translateDomain->id],
											['escape' => false, 'class' => 'btn btn-outline-primary', 'title' => __d('translate', 'View')],
										) ?>
										<?= $this->Html->link(
											$this->Icon->render('edit'),
											['action' => 'edit', $translateDomain->id],
											['escape' => false, 'class' => 'btn btn-outline-secondary', 'title' => __d('translate', 'Edit')],
										) ?>
										<?= $this->Form->postLink(
											$this->Icon->render('delete'),
											['action' => 'delete', $translateDomain->id],
											['escape' => false, 'confirm' => __d('translate', 'Are you sure you want to delete # {0}?', $translateDomain->id), 'class' => 'btn btn-outline-danger', 'title' => __d('translate', 'Delete')],
										) ?>
									</div>
								</td>
							</tr>
							<?php } ?>
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
