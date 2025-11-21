<?php
/**
 * @var \App\View\AppView $this
 * @var mixed $poFiles
 * @var mixed $potFiles
 * @var string $localePath
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
				<?= $this->Html->link('<i class="fas fa-search-plus"></i> ' . __d('translate', 'Analyze PO File'), ['action' => 'analyze'], ['class' => 'list-group-item list-group-item-action', 'escape' => false]) ?>
				<?= $this->Html->link('<i class="fas fa-flask"></i> ' . __d('translate', 'Run i18n Extract'), ['action' => 'runExtract'], ['class' => 'list-group-item list-group-item-action', 'escape' => false]) ?>
			</div>
		</div>
	</aside>

	<div class="col-md-9 col-sm-8 col-12">
		<div class="card">
			<div class="card-header">
				<h3 class="card-title"><i class="fa-solid fa-file-import"></i> <?= __d('translate', 'Extract Translations') ?></h3>
			</div>
			<div class="card-body">
				<div class="alert alert-info mb-3">
					<i class="fas fa-folder-open"></i>
					<strong><?= __d('translate', 'Locale Path:') ?></strong>
					<code><?= h($localePath) ?></code>
				</div>
				<?= $this->Form->create(null) ?>
					<fieldset>
						<legend><?= __d('translate', 'From POT File') ?></legend>
						<div class="mb-2">
							<button type="button" class="btn btn-sm btn-outline-secondary select-all" data-target="sel-pot"><?= __d('translate', 'Select All') ?></button>
							<button type="button" class="btn btn-sm btn-outline-secondary deselect-all" data-target="sel-pot"><?= __d('translate', 'Deselect All') ?></button>
						</div>
						<div id="sel-pot">
							<?= $this->Form->control('sel_pot', [
								'multiple' => 'checkbox',
								'label' => __d('translate', 'Selection'),
								'options' => $potFiles,
							]) ?>
						</div>
					</fieldset>

					<fieldset class="mt-4">
						<legend><?= __d('translate', 'From PO File') ?></legend>
						<div class="mb-2">
							<button type="button" class="btn btn-sm btn-outline-secondary select-all" data-target="sel-po"><?= __d('translate', 'Select All') ?></button>
							<button type="button" class="btn btn-sm btn-outline-secondary deselect-all" data-target="sel-po"><?= __d('translate', 'Deselect All') ?></button>
						</div>
						<div id="sel-po">
							<?= $this->Form->control('sel_po', [
								'multiple' => 'checkbox',
								'label' => __d('translate', 'Selection'),
								'options' => $poFiles,
							]) ?>
						</div>
					</fieldset>

					<div class="mt-3">
						<?= $this->Form->submit(__d('translate', 'Submit'), ['class' => 'btn btn-primary']) ?>
					</div>
				<?= $this->Form->end() ?>
			</div>
		</div>
	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
	document.querySelectorAll('.select-all').forEach(function(btn) {
		btn.addEventListener('click', function() {
			var target = document.getElementById(this.dataset.target);
			target.querySelectorAll('input[type="checkbox"]').forEach(function(cb) {
				cb.checked = true;
			});
		});
	});

	document.querySelectorAll('.deselect-all').forEach(function(btn) {
		btn.addEventListener('click', function() {
			var target = document.getElementById(this.dataset.target);
			target.querySelectorAll('input[type="checkbox"]').forEach(function(cb) {
				cb.checked = false;
			});
		});
	});
});
</script>
