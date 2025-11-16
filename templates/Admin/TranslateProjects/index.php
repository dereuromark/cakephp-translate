<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\Translate\Model\Entity\TranslateProject> $translateProjects
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
					'<i class="fas fa-plus-circle"></i> ' . __d('translate', 'New Translate Project'),
					['action' => 'add'],
					['escape' => false, 'class' => 'list-group-item list-group-item-action'],
				) ?>
			</div>
		</div>
	</nav>

	<!-- Main Content -->
	<div class="col-lg-9 col-md-8">
		<div class="page-header mb-4">
			<h1><i class="fas fa-project-diagram"></i> <?= __d('translate', 'Translate Projects') ?></h1>
		</div>

		<!-- Results Table -->
		<div class="card">
			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-hover">
						<thead>
							<tr>
								<th><?= $this->Paginator->sort('name') ?></th>
								<th><?= $this->Paginator->sort('type') ?></th>
								<th class="text-center"><?= $this->Paginator->sort('default') ?></th>
								<th><?= $this->Paginator->sort('status') ?></th>
								<th><?= $this->Paginator->sort('created', null, ['direction' => 'desc']) ?></th>
								<th><?= $this->Paginator->sort('modified', null, ['direction' => 'desc']) ?></th>
								<th class="actions text-center"><?= __d('translate', 'Actions') ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($translateProjects as $translateProject) : ?>
							<tr>
								<td>
									<strong><?= h($translateProject->name) ?></strong>
									<?php if ($translateProject->default) { ?>
										<span class="badge bg-primary ms-1"><?= __d('translate', 'Default') ?></span>
									<?php } ?>
								</td>
								<td>
									<?= $translateProject::types($translateProject->type) ?>
									<div>
										<small>
											<?php if ($translateProject->path) { ?>
												<?php
												$path = $translateProject->path;
												if (mb_strlen($path) > 38) {
													$path = '...' . mb_substr($path, -35);
												}
												echo h($path);
												?>
											<?php } ?>

										</small>
									</div>
								</td>
								<td class="text-center">
									<?= $this->element('Translate.yes_no', ['value' => $translateProject->default]) ?>
								</td>
								<td>
									<?php
									$status = $translateProject::statuses($translateProject->status);
									$statusClass = $translateProject->status ? 'success' : 'secondary';
									?>
									<span class="badge bg-<?= $statusClass ?>"><?= $status ?></span>
								</td>
								<td>
									<small><?= $this->Time->nice($translateProject->created) ?></small>
								</td>
								<td>
									<small><?= $this->Time->nice($translateProject->modified) ?></small>
								</td>
								<td class="actions text-center">
									<div class="btn-group btn-group-sm" role="group">
										<?= $this->Html->link(
											$this->Icon->render('view'),
											['action' => 'view', $translateProject->id],
											['escape' => false, 'class' => 'btn btn-outline-primary', 'title' => __d('translate', 'View')],
										) ?>
										<?= $this->Html->link(
											$this->Icon->render('edit'),
											['action' => 'edit', $translateProject->id],
											['escape' => false, 'class' => 'btn btn-outline-secondary', 'title' => __d('translate', 'Edit')],
										) ?>
										<?= $this->Form->postLink(
											$this->Icon->render('delete'),
											['action' => 'delete', $translateProject->id],
											['escape' => false, 'confirm' => __d('translate', 'Are you sure you want to delete # {0}?', $translateProject->id), 'class' => 'btn btn-outline-danger', 'title' => __d('translate', 'Delete')],
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
