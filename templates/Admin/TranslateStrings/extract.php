<?php
/**
 * @var \App\View\AppView $this
 * @var mixed $poFiles
 * @var mixed $potFiles
 */
?>
<div class="row">
	<aside class="col-md-3 col-sm-4 col-12">
		<div class="card">
			<div class="card-header">
				<h3 class="card-title"><i class="fa-solid fa-bars"></i> <?= __d('translate', 'Actions') ?></h3>
			</div>
			<div class="list-group list-group-flush">
				<?= $this->Html->link(__d('translate', 'List Translate Strings'), ['action' => 'index'], ['class' => 'list-group-item list-group-item-action']) ?>
			</div>
		</div>
	</aside>

	<div class="col-md-9 col-sm-8 col-12">
		<div class="card">
			<div class="card-header">
				<h3 class="card-title"><i class="fa-solid fa-file-import"></i> <?= __d('translate', 'Extract Translations') ?></h3>
			</div>
			<div class="card-body">
				<?= $this->Form->create(null) ?>
					<fieldset>
						<legend><?= __d('translate', 'From POT File') ?></legend>
						<?= $this->Form->control('sel_pot', [
							'multiple' => 'checkbox',
							'label' => __d('translate', 'Selection'),
							'options' => $potFiles,
						]) ?>
					</fieldset>

					<fieldset class="mt-4">
						<legend><?= __d('translate', 'From PO File') ?></legend>
						<?= $this->Form->control('sel_po', [
							'multiple' => 'checkbox',
							'label' => __d('translate', 'Selection'),
							'options' => $poFiles,
						]) ?>
					</fieldset>

					<div class="mt-3">
						<?= $this->Form->submit(__d('translate', 'Submit'), ['class' => 'btn btn-primary']) ?>
					</div>
				<?= $this->Form->end() ?>
			</div>
		</div>
	</div>
</div>
