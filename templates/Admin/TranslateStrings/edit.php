<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateString $translateString
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
				<?= $this->Form->postLink(
					'<i class="fas fa-trash"></i> ' . __d('translate', 'Delete'),
					['action' => 'delete', $translateString->id],
					['confirm' => __d('translate', 'Are you sure you want to delete # {0}?', $translateString->id), 'escape' => false, 'class' => 'list-group-item list-group-item-action text-danger'],
				) ?>
				<?= $this->Html->link(
					'<i class="fas fa-list"></i> ' . __d('translate', 'List Translate Strings'),
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
					<i class="fas fa-edit"></i>
					<?= __d('translate', 'Edit Translate String') ?>
				</h2>
			</div>
			<div class="card-body">
				<?= $this->Form->create($translateString) ?>
				<fieldset>
					<div class="row g-3">
						<div class="col-md-12">
							<?= $this->Form->control('translate_domain_id', ['label' => '<i class="fas fa-folder"></i> ' . __d('translate', 'Domain'), 'escape' => false]) ?>
						</div>

						<div class="col-md-12">
							<?= $this->Form->control('name', ['label' => '<i class="fas fa-tag"></i> ' . __d('translate', 'Name'), 'escape' => false]) ?>
						</div>

						<div class="col-md-6">
							<?= $this->Form->control('plural', ['label' => '<i class="fas fa-list-ol"></i> ' . __d('translate', 'Plural'), 'escape' => false]) ?>
						</div>

						<div class="col-md-6">
							<?= $this->Form->control('context', ['label' => '<i class="fas fa-info-circle"></i> ' . __d('translate', 'Context'), 'escape' => false]) ?>
						</div>

						<div class="col-md-6">
							<div class="form-check form-switch">
								<?= $this->Form->control('is_html', ['type' => 'checkbox', 'label' => '<i class="fas fa-code"></i> ' . __d('translate', 'Is HTML'), 'escape' => false, 'class' => 'form-check-input']) ?>
							</div>
						</div>

						<div class="col-md-6">
							<div class="form-check form-switch">
								<?= $this->Form->control('translate_afterwards', ['type' => 'checkbox', 'label' => '<i class="fas fa-forward"></i> ' . __d('translate', 'Translate Afterwards'), 'escape' => false, 'class' => 'form-check-input']) ?>
							</div>
						</div>
					</div>
				</fieldset>

				<div class="mt-4 d-flex gap-2">
					<?= $this->Form->button(
						'<i class="fas fa-save"></i> ' . __d('translate', 'Submit'),
						['class' => 'btn btn-primary', 'escape' => false],
					) ?>
					<?= $this->Html->link(
						'<i class="fas fa-times"></i> ' . __d('translate', 'Cancel'),
						['action' => 'index'],
						['class' => 'btn btn-outline-secondary', 'escape' => false],
					) ?>
				</div>
				<?= $this->Form->end() ?>
			</div>
		</div>
	</div>
</div>
