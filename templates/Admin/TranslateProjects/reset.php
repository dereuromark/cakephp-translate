<?php
/**
 * @var \App\View\AppView $this
 * @var mixed $languages
 * @var mixed $removeOptions
 */
?>
<div class="row">
	<!-- Sidebar -->
	<nav class="col-lg-3 col-md-4 mb-4">
		<div class="card mb-3">
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

		<div class="card">
			<div class="card-header">
				<i class="fas fa-exclamation-triangle"></i> <?= __d('translate', 'Warning') ?>
			</div>
			<div class="card-body">
				<p class="small text-danger mb-2">
					<strong><?= __d('translate', 'This action cannot be undone!') ?></strong>
				</p>
				<p class="small text-muted mb-0">
					<?= __d('translate', 'Resetting will permanently remove selected data from the translation project. Make sure you have backups before proceeding.') ?>
				</p>
			</div>
		</div>
	</nav>

	<!-- Main Content -->
	<div class="col-lg-9 col-md-8">
		<div class="card">
			<div class="card-header">
				<h2 class="mb-0">
					<i class="fas fa-sync-alt"></i>
					<?= __d('translate', 'Reset Translate Project') ?>
				</h2>
			</div>
			<div class="card-body">
				<?= $this->Form->create(null) ?>
				<fieldset>
					<div class="row g-3">
						<div class="col-md-12">
							<label class="form-label">
								<i class="fas fa-trash-alt"></i> <?= __d('translate', 'Reset Options') ?>
							</label>
							<?= $this->Form->control('reset', [
								'multiple' => 'checkbox',
								'options' => $removeOptions,
								'label' => false,
							]) ?>
							<small class="form-text text-muted">
								<?= __d('translate', 'Select which data to remove from this project') ?>
							</small>
						</div>

						<div class="col-md-12">
							<label class="form-label">
								<i class="fas fa-language"></i> <?= __d('translate', 'Languages') ?>
							</label>
							<?= $this->Form->control('language', [
								'multiple' => 'checkbox',
								'options' => $languages,
								'label' => false,
							]) ?>
							<small class="form-text text-muted">
								<?= __d('translate', 'Select languages (only relevant for resetting terms)') ?>
							</small>
						</div>
					</div>
				</fieldset>

				<div class="mt-4 d-flex gap-2">
					<?= $this->Form->button(
						'<i class="fas fa-sync-alt"></i> ' . __d('translate', 'Submit'),
						['class' => 'btn btn-danger', 'escapeTitle' => false],
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
