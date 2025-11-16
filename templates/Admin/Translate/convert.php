<?php
/**
 * @var \App\View\AppView $this
 * @var mixed $text
 * @var mixed $translate
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
		<?php if (!empty($text)) { ?>
		<div class="card mb-3">
			<div class="card-header">
				<h3 class="card-title"><i class="fas fa-check-circle"></i> <?= __d('translate', 'Result') ?></h3>
			</div>
			<div class="card-body">
				<?= $this->Form->control('text', ['value' => $text, 'class' => 'form-control', 'type' => 'textarea', 'rows' => 5, 'label' => false]) ?>
			</div>
		</div>
		<?php } ?>

		<div class="card">
			<div class="card-header">
				<h3 class="card-title"><i class="fas fa-exchange-alt"></i> <?= __d('translate', 'Convert text') ?></h3>
			</div>
			<div class="card-body">
				<?= $this->Form->create() ?>
				<fieldset>
					<?php
						echo $this->Form->control('direction', ['type' => 'select', 'options' => ['From Text to PO content', 'From PO content to text']]);
						echo $this->Form->control('quotes', ['type' => 'select', 'options' => ['Do nothing', 'Remove smart quotes', 'Add smart quotes']]);
						echo $this->Form->control('newline', ['type' => 'select', 'options' => ['\n', '<br/>']]);
						echo $this->Form->control('input', ['type' => 'textarea', 'rows' => 20]);
					?>
				</fieldset>
				<div class="form-group">
					<?= $this->Form->button('<i class="fas fa-save"></i> ' . __d('translate', 'Submit'), ['class' => 'btn btn-primary', 'escape' => false]) ?>
				</div>
				<?= $this->Form->end() ?>
			</div>
		</div>
	</div>
</div>
