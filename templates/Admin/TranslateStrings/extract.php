<?php
/**
 * @var \App\View\AppView $this
 * @var mixed $poFiles
 * @var mixed $potFiles
 * @var string $localePath
 * @var array<string> $preselectedPot
 * @var array<string> $preselectedPo
 * @var string $preselectDomain
 */
$cspNonce = (string)$this->getRequest()->getAttribute('cspNonce', '');
$preselectedPot = $preselectedPot ?? [];
$preselectedPo = $preselectedPo ?? [];
$preselectDomain = $preselectDomain ?? '';
?>
<div class="row">
	<aside class="col-md-3 col-sm-4 col-12">
		<div class="card">
			<div class="card-header">
				<h3 class="card-title"><i class="fa-solid fa-bars"></i> <?= __d('translate', 'Actions') ?></h3>
			</div>
			<div class="list-group list-group-flush">
				<?= $this->Html->link(__d('translate', 'List Translate Strings'), ['action' => 'index'], ['class' => 'list-group-item list-group-item-action']) ?>
				<?= $this->Html->link('<i class="fas fa-search-plus"></i> ' . __d('translate', 'Analyze PO File'), ['action' => 'analyze'], ['class' => 'list-group-item list-group-item-action', 'escapeTitle' => false]) ?>
				<?= $this->Html->link('<i class="fas fa-flask"></i> ' . __d('translate', 'Run i18n Extract'), ['action' => 'runExtract'], ['class' => 'list-group-item list-group-item-action', 'escapeTitle' => false]) ?>
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
				<?php if ($preselectDomain !== '' && ($preselectedPot || $preselectedPo)) { ?>
					<div class="alert alert-primary mb-3">
						<i class="fas fa-info-circle"></i>
						<?= __d('translate', 'Pre-selected the {0} domain (linked from Detected Domains analyzer).', '<code>' . h($preselectDomain) . '</code>') ?>
					</div>
				<?php } ?>
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
								'default' => $preselectedPot,
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
								'default' => $preselectedPo,
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

<script<?= $cspNonce !== '' ? ' nonce="' . h($cspNonce) . '"' : '' ?>>
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
