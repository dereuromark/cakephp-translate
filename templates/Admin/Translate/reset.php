<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateDomain $translateDomain
 */
?>

<div class="row">
	<aside class="col-md-3">
		<div class="card">
			<div class="card-header">
				<h3 class="card-title"><i class="fas fa-bars"></i> <?= __d('translate', 'Actions') ?></h3>
			</div>
			<div class="list-group list-group-flush">
				<?= $this->Html->link('<i class="fas fa-tachometer-alt"></i> ' . __d('translate', 'Overview'), ['action' => 'index'], ['escape' => false, 'class' => 'list-group-item list-group-item-action']) ?>
			</div>
		</div>
	</aside>

	<div class="col-md-9">
		<div class="card">
			<div class="card-header">
				<h3 class="card-title"><i class="fas fa-trash-alt"></i> <?= __d('translate', 'Reset') ?></h3>
			</div>
			<div class="card-body">
				<?= $this->Form->create(null) ?>
				<fieldset>
					<legend><?= __d('translate', 'Reset') ?></legend>
					<?php
						$resetOptions = [
							'terms' => 'terms',
							'strings' => 'strings',
							'groups' => 'groups',
							'languages' => 'languages',
						];
						echo $this->Form->control('selection', ['multiple' => 'checkbox', 'label' => __d('translate', 'Selection'), 'options' => $resetOptions]);
					?>
				</fieldset>

				<div class="form-group">
					<?= $this->Form->button('<i class="fas fa-redo"></i> ' . __d('translate', 'Reset'), ['class' => 'btn btn-warning', 'escapeTitle' => false]) ?>
				</div>
				<?= $this->Form->end() ?>

				<hr>

				<p class="text-muted">
					<?= __d('translate', 'or') ?>
				</p>

				<div class="btn-group">
					<?= $this->Form->postLink(
						'<i class="fas fa-exclamation-triangle"></i> ' . __d('translate', 'Hard reset Translate (fully truncate all tables)'),
						['?' => ['hard-reset' => '1']],
						['confirm' => __d('translate', 'Sure?'), 'class' => 'btn btn-danger', 'escapeTitle' => false]
					) ?>
				</div>
			</div>
		</div>
	</div>
</div>
