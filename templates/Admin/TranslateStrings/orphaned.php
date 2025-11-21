<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\Translate\Model\Entity\TranslateString> $translateStrings
 * @var int $count
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
					'<i class="fas fa-list"></i> ' . __d('translate', 'All Strings'),
					['action' => 'index'],
					['escape' => false, 'class' => 'list-group-item list-group-item-action'],
				) ?>
			</div>
		</div>

		<div class="card mt-3">
			<div class="card-header">
				<i class="fas fa-info-circle"></i> <?= __d('translate', 'Info') ?>
			</div>
			<div class="card-body">
				<p class="card-text small text-muted">
					<?= __d('translate', 'Orphaned strings are translation strings that no longer have references to source code files. This usually means the original code was removed or refactored.') ?>
				</p>
				<p class="card-text small text-muted">
					<?= __d('translate', 'You can safely delete these strings if they are no longer needed, or keep them if they were manually added.') ?>
				</p>
			</div>
		</div>
	</nav>

	<!-- Main Content -->
	<div class="col-lg-9 col-md-8">
		<div class="page-header mb-4">
			<h1>
				<i class="fas fa-unlink"></i> <?= __d('translate', 'Orphaned Strings') ?>
				<span class="badge bg-secondary"><?= $count ?></span>
			</h1>
		</div>

		<?php if ($count > 0) { ?>
		<!-- Bulk Actions -->
		<?= $this->Form->create(null, ['id' => 'orphaned-form']) ?>
		<?= $this->Form->hidden('bulk_action', ['id' => 'bulk-action']) ?>

		<div class="card mb-3">
			<div class="card-body py-2">
				<div class="d-flex justify-content-between align-items-center">
					<span class="text-muted">
						<i class="fas fa-info-circle"></i>
						<?= __d('translate', 'Actions apply to all {0} orphaned strings', $count) ?>
					</span>
					<div class="btn-group">
						<?= $this->Form->button(
							'<i class="fas fa-eye-slash"></i> ' . __d('translate', 'Mark All Inactive'),
							[
								'type' => 'button',
								'class' => 'btn btn-warning btn-sm bulk-action-btn',
								'escapeTitle' => false,
								'data-action' => 'deactivate',
								'data-confirm' => __d('translate', 'Mark all {0} orphaned strings as inactive?', $count),
							],
						) ?>
						<?= $this->Form->button(
							'<i class="fas fa-trash"></i> ' . __d('translate', 'Delete All'),
							[
								'type' => 'button',
								'class' => 'btn btn-danger btn-sm bulk-action-btn',
								'escapeTitle' => false,
								'data-action' => 'delete',
								'data-confirm' => __d('translate', 'Are you sure you want to delete all {0} orphaned strings? This cannot be undone.', $count),
							],
						) ?>
					</div>
				</div>
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
								<th><?= __d('translate', 'Domain') ?></th>
								<th class="text-center"><?= $this->Paginator->sort('active') ?></th>
								<th><?= $this->Paginator->sort('last_import', null, ['direction' => 'desc']) ?></th>
								<th><?= $this->Paginator->sort('modified', null, ['direction' => 'desc']) ?></th>
								<th class="text-center"><?= __d('translate', 'Actions') ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($translateStrings as $translateString) { ?>
							<tr>
								<td>
									<span title="<?= h($translateString->name) ?>">
										<?= h($this->Text->truncate($translateString->name, 80)) ?>
									</span>
									<?php if ($translateString->context) { ?>
										<br><small class="text-muted">
											<i class="fas fa-tag"></i> <?= h($translateString->context) ?>
										</small>
									<?php } ?>
								</td>
								<td>
									<span class="badge bg-dark">
										<i class="fas fa-folder"></i>
										<?= h($translateString->_matchingData['TranslateDomains']->name) ?>
									</span>
								</td>
								<td class="text-center">
									<?= $this->element('Translate.yes_no', ['value' => $translateString->active]) ?>
								</td>
								<td><small><?= $this->Time->nice($translateString->last_import) ?></small></td>
								<td><small><?= $this->Time->nice($translateString->modified) ?></small></td>
								<td class="text-center">
									<div class="btn-group btn-group-sm" role="group">
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
											['escape' => false, 'class' => 'btn btn-outline-danger', 'confirm' => __d('translate', 'Are you sure you want to delete # {0}?', $translateString->id), 'title' => __d('translate', 'Delete'), 'data-bs-toggle' => 'tooltip', 'block' => true],
										); ?>
									</div>
								</td>
							</tr>
							<?php } ?>
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

		<?= $this->Form->end() ?>
		<?php } else { ?>
		<div class="card">
			<div class="card-body text-center py-5">
				<i class="fas fa-check-circle fa-3x text-success mb-3"></i>
				<h4><?= __d('translate', 'No orphaned strings found') ?></h4>
				<p class="text-muted"><?= __d('translate', 'All translation strings have references to source code.') ?></p>
			</div>
		</div>
		<?php } ?>
	</div>
</div>

<?= $this->fetch('postLink') ?>

<script>
// Handle bulk action buttons
document.querySelectorAll('.bulk-action-btn').forEach(function(button) {
	button.addEventListener('click', function(e) {
		if (confirm(this.dataset.confirm)) {
			document.getElementById('bulk-action').value = this.dataset.action;
			document.getElementById('orphaned-form').submit();
		}
	});
});
</script>
