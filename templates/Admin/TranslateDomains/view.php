<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateDomain $translateDomain
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
					'<i class="fas fa-edit"></i> ' . __d('translate', 'Edit Translate Domain'),
					['action' => 'edit', $translateDomain->id],
					['escape' => false, 'class' => 'list-group-item list-group-item-action'],
				) ?>
				<?= $this->Form->postLink(
					'<i class="fas fa-trash"></i> ' . __d('translate', 'Delete Translate Domain'),
					['action' => 'delete', $translateDomain->id],
					['confirm' => __d('translate', 'Are you sure you want to delete # {0}?', $translateDomain->id), 'escape' => false, 'class' => 'list-group-item list-group-item-action text-danger'],
				) ?>
				<?= $this->Html->link(
					'<i class="fas fa-list"></i> ' . __d('translate', 'List Translate Domains'),
					['action' => 'index'],
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
			<h1><i class="fas fa-folder"></i> <?= h($translateDomain->name) ?></h1>
		</div>

		<!-- Domain Details -->
		<div class="card mb-4">
			<div class="card-header">
				<i class="fas fa-info-circle"></i> <?= __d('translate', 'Domain Details') ?>
			</div>
			<div class="card-body">
				<table class="table table-borderless">
					<tr>
						<th width="200"><?= __d('translate', 'Name') ?></th>
						<td><?= h($translateDomain->name) ?></td>
					</tr>
					<tr>
						<th><?= __d('translate', 'Translate Project') ?></th>
						<td>
							<?php if ($translateDomain->has('translate_project')) { ?>
								<?= $this->Html->link(
									$translateDomain->translate_project->name,
									['controller' => 'TranslateProjects', 'action' => 'view', $translateDomain->translate_project->id],
								) ?>
							<?php } ?>
						</td>
					</tr>
					<tr>
						<th><?= __d('translate', 'Active') ?></th>
						<td><?= $this->element('Translate.yes_no', ['value' => $translateDomain->active]) ?></td>
					</tr>
					<tr>
						<th><?= __d('translate', 'Prio') ?></th>
						<td><?= $this->Number->format($translateDomain->prio) ?></td>
					</tr>
					<tr>
						<th><?= __d('translate', 'Created') ?></th>
						<td><?= $this->Time->nice($translateDomain->created) ?></td>
					</tr>
					<tr>
						<th><?= __d('translate', 'Modified') ?></th>
						<td><?= $this->Time->nice($translateDomain->modified) ?></td>
					</tr>
				</table>
			</div>
		</div>
	</div>
</div>
