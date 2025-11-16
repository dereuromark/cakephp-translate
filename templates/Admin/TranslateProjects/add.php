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
					'<i class="fas fa-list"></i> ' . __d('translate', 'List Translate Projects'),
					['action' => 'index'],
					['escape' => false, 'class' => 'list-group-item list-group-item-action'],
				) ?>
			</div>
		</div>
	</nav>

	<!-- Main Content -->
	<div class="col-lg-9 col-md-8">
		<div class="card">
			<div class="card-header">
				<h2 class="mb-0">
					<i class="fas fa-plus-circle"></i>
					<?= __d('translate', 'Add Translate Project') ?>
				</h2>
			</div>
			<div class="card-body">
				<?= $this->Form->create($translateProject) ?>
				<fieldset>
					<div class="row g-3">
						<div class="col-md-12">
							<?= $this->Form->control('name', [
								'label' => '<i class="fas fa-tag"></i> ' . __d('translate', 'Name'),
								'escape' => false,
							]) ?>
						</div>

						<div class="col-md-6">
							<?= $this->Form->control('type', [
								'options' => $translateProject::types(),
								'label' => '<i class="fas fa-cog"></i> ' . __d('translate', 'Type'),
								'escape' => false,
							]) ?>
						</div>

						<div class="col-md-6">
							<?= $this->Form->control('status', [
								'options' => $translateProject::statuses(),
								'label' => '<i class="fas fa-toggle-on"></i> ' . __d('translate', 'Status'),
								'escape' => false,
							]) ?>
						</div>

						<div class="col-md-12">
							<?= $this->Form->control('path', [
								'label' => '<i class="fas fa-folder"></i> ' . __d('translate', 'Path'),
								'escape' => false,
								'placeholder' => __d('translate', 'e.g., plugins/MyPlugin or leave empty for default app path'),
							]) ?>
							<small class="form-text text-muted">
								<?= __d('translate', 'Optional: Relative or absolute path for this project (e.g., plugins/MyPlugin). Leave empty to use default app path.') ?>
							</small>
						</div>

						<div class="col-md-12">
							<div class="form-check form-switch">
								<?= $this->Form->control('default', [
									'type' => 'checkbox',
									'label' => '<i class="fas fa-star"></i> ' . __d('translate', 'Default Project'),
									'escape' => false,
									'class' => 'form-check-input',
								]) ?>
							</div>
							<small class="form-text text-muted">
								<?= __d('translate', 'Set this as the default translation project') ?>
							</small>
						</div>
					</div>
				</fieldset>

				<div class="mt-4 d-flex gap-2">
					<?= $this->Form->button(
						'<i class="fas fa-save"></i> ' . __d('translate', 'Submit'),
						['class' => 'btn btn-primary', 'escapeTitle' => false],
					) ?>
					<?= $this->Html->link(
						'<i class="fas fa-times"></i> ' . __d('translate', 'Cancel'),
						['action' => 'index'],
						['class' => 'btn btn-outline-secondary', 'escapeTitle' => false],
					) ?>
				</div>
				<?= $this->Form->end() ?>
			</div>
		</div>
	</div>
</div>
