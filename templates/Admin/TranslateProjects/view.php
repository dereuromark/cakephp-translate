<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateProject $translateProject
 */
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
					'<i class="fas fa-edit"></i> ' . __d('translate', 'Edit Translate Project'),
					['action' => 'edit', $translateProject->id],
					['escape' => false, 'class' => 'list-group-item list-group-item-action'],
				) ?>
				<?= $this->Form->postLink(
					'<i class="fas fa-trash"></i> ' . __d('translate', 'Delete Translate Project'),
					['action' => 'delete', $translateProject->id],
					['confirm' => __d('translate', 'Are you sure you want to delete # {0}?', $translateProject->id), 'escape' => false, 'class' => 'list-group-item list-group-item-action text-danger'],
				) ?>
				<?= $this->Html->link(
					'<i class="fas fa-list"></i> ' . __d('translate', 'List Translate Projects'),
					['action' => 'index'],
					['escape' => false, 'class' => 'list-group-item list-group-item-action'],
				) ?>
			</div>
		</div>
	</nav>

	<!-- Main Content -->
	<div class="col-lg-9 col-md-8">
		<div class="page-header mb-4">
			<h1><i class="fas fa-project-diagram"></i> <?= h($translateProject->name) ?></h1>
		</div>

		<!-- Project Details -->
		<div class="card mb-4">
			<div class="card-header">
				<i class="fas fa-info-circle"></i> <?= __d('translate', 'Project Details') ?>
			</div>
			<div class="card-body">
				<table class="table table-borderless">
					<tr>
						<th width="200"><?= __d('translate', 'Name') ?></th>
						<td><?= h($translateProject->name) ?></td>
					</tr>
					<tr>
						<th><?= __d('translate', 'Type') ?></th>
						<td><?= $translateProject::types($translateProject->type) ?></td>
					</tr>
					<tr>
						<th><?= __d('translate', 'Default') ?></th>
						<td>
							<?= $this->element('Translate.yes_no', ['value' => $translateProject->default]) ?>
							<?php if ($translateProject->default) { ?>
								<span class="badge bg-primary ms-2"><?= __d('translate', 'Default Project') ?></span>
							<?php } ?>
						</td>
					</tr>
					<tr>
						<th><?= __d('translate', 'Status') ?></th>
						<td>
							<?php
							$status = $translateProject::statuses($translateProject->status);
							$statusClass = $translateProject->status ? 'success' : 'secondary';
							?>
							<span class="badge bg-<?= $statusClass ?>"><?= $status ?></span>
						</td>
					</tr>
					<tr>
						<th><?= __d('translate', 'Path') ?></th>
						<td>
							<?php if ($translateProject->path) { ?>
								<code><?= h($translateProject->path) ?></code>
							<?php } else { ?>
								<span class="text-muted"><?= __d('translate', 'Default app path') ?></span>
							<?php } ?>
						</td>
					</tr>
					<tr>
						<th><?= __d('translate', 'Created') ?></th>
						<td><?= $this->Time->nice($translateProject->created) ?></td>
					</tr>
					<tr>
						<th><?= __d('translate', 'Modified') ?></th>
						<td><?= $this->Time->nice($translateProject->modified) ?></td>
					</tr>
				</table>
			</div>
		</div>

		<!-- Related Domains -->
		<?php if (!empty($translateProject->translate_domains)) { ?>
		<div class="card">
			<div class="card-header">
				<i class="fas fa-folder"></i> <?= __d('translate', 'Related Translate Domains') ?>
				<span class="badge bg-secondary ms-2"><?= count($translateProject->translate_domains) ?></span>
			</div>
			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-hover">
						<thead>
							<tr>
								<th><?= __d('translate', 'Name') ?></th>
								<th><?= __d('translate', 'Active') ?></th>
								<th><?= __d('translate', 'Priority') ?></th>
								<th><?= __d('translate', 'Created') ?></th>
								<th><?= __d('translate', 'Modified') ?></th>
								<th class="actions text-center"><?= __d('translate', 'Actions') ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($translateProject->translate_domains as $translateDomain) { ?>
							<tr>
								<td>
									<strong><?= h($translateDomain->name) ?></strong>
								</td>
								<td>
									<?= $this->element('Translate.yes_no', ['value' => $translateDomain->active]) ?>
								</td>
								<td><?= h($translateDomain->prio) ?></td>
								<td><small><?= $this->Time->nice($translateDomain->created) ?></small></td>
								<td><small><?= $this->Time->nice($translateDomain->modified) ?></small></td>
								<td class="actions text-center">
									<div class="btn-group btn-group-sm" role="group">
										<?= $this->Html->link(
											$this->Icon->render('view'),
											['controller' => 'TranslateDomains', 'action' => 'view', $translateDomain->id],
											['escape' => false, 'class' => 'btn btn-outline-primary', 'title' => __d('translate', 'View')],
										) ?>
										<?= $this->Html->link(
											$this->Icon->render('edit'),
											['controller' => 'TranslateDomains', 'action' => 'edit', $translateDomain->id],
											['escape' => false, 'class' => 'btn btn-outline-secondary', 'title' => __d('translate', 'Edit')],
										) ?>
										<?= $this->Form->postLink(
											$this->Icon->render('delete'),
											['controller' => 'TranslateDomains', 'action' => 'delete', $translateDomain->id],
											['confirm' => __d('translate', 'Are you sure you want to delete # {0}?', $translateDomain->id), 'escape' => false, 'class' => 'btn btn-outline-danger', 'title' => __d('translate', 'Delete')],
										) ?>
									</div>
								</td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<?php } ?>
	</div>
</div>
