<?php
/**
 * @var \App\View\AppView $this
 * @var mixed $poFiles
 * @var mixed $potFiles
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
					'<i class="fas fa-list"></i> ' . __d('translate', 'List Translate Domains'),
					['action' => 'index'],
					['escape' => false, 'class' => 'list-group-item list-group-item-action'],
				) ?>
			</div>
		</div>

		<div class="card mt-3">
			<div class="card-header">
				<i class="fas fa-info-circle"></i> <?= __d('translate', 'Information') ?>
			</div>
			<div class="card-body">
				<h6 class="fw-bold"><?= __d('translate', 'POT Files') ?></h6>
				<p class="small text-muted mb-3">
					<?= __d('translate', 'POT (Portable Object Template) files contain the source strings to be translated.') ?>
				</p>

				<h6 class="fw-bold"><?= __d('translate', 'PO Files') ?></h6>
				<p class="small text-muted mb-3">
					<?= __d('translate', 'PO (Portable Object) files contain translations for a specific language.') ?>
				</p>

				<h6 class="fw-bold"><?= __d('translate', 'Source Code') ?></h6>
				<p class="small text-muted mb-0">
					<?= __d('translate', 'Extract translation strings directly from your application source code.') ?>
				</p>
			</div>
		</div>
	</nav>

	<!-- Main Content -->
	<div class="col-lg-9 col-md-8">
		<div class="card">
			<div class="card-header">
				<h2 class="mb-0">
					<i class="fas fa-download"></i>
					<?= __d('translate', 'Extract Translation Strings') ?>
				</h2>
			</div>
			<div class="card-body">
				<?= $this->Form->create(null) ?>

				<fieldset class="mb-4">
					<legend><i class="fas fa-file-code"></i> <?= __d('translate', 'From POT File') ?></legend>
					<?php
					echo $this->Form->control('sel_pot', [
						'multiple' => 'checkbox',
						'label' => __d('translate', 'Selection'),
						'options' => $potFiles,
					]);
					?>
				</fieldset>

				<fieldset class="mb-4">
					<legend><i class="fas fa-language"></i> <?= __d('translate', 'From PO File') ?></legend>
					<?php
					echo $this->Form->control('sel_po', [
						'multiple' => 'checkbox',
						'label' => __d('translate', 'Selection'),
						'options' => $poFiles,
					]);
					?>
				</fieldset>

				<fieldset class="mb-4">
					<legend><i class="fas fa-code"></i> <?= __d('translate', 'From Source Code') ?></legend>
					<div class="form-check form-switch">
						<?php
						echo $this->Form->control('source_code', [
							'type' => 'checkbox',
							'label' => __d('translate', 'Extract from source code'),
							'class' => 'form-check-input',
						]);
						?>
					</div>
				</fieldset>

				<fieldset class="mb-4">
					<legend><i class="fas fa-gamepad"></i> <?= __d('translate', 'Controller Names') ?></legend>
					<div class="form-check form-switch">
						<?php
						echo $this->Form->control('controller_names', [
							'type' => 'checkbox',
							'label' => __d('translate', 'Extract controller names'),
							'class' => 'form-check-input',
						]);
						?>
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
