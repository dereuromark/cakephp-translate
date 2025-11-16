<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateTerm $translateTerm
 * @var mixed $translateLocales
 * @var mixed $translateStrings
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
				<?= $this->Form->postLink(
					'<i class="fas fa-trash"></i> ' . __d('translate', 'Delete'),
					['action' => 'delete', $translateTerm->id],
					['confirm' => __d('translate', 'Are you sure you want to delete # {0}?', $translateTerm->id), 'escape' => false, 'class' => 'list-group-item list-group-item-action text-danger', 'block' => true],
				) ?>
				<?= $this->Html->link(
					'<i class="fas fa-comments"></i> ' . __d('translate', 'List Translate Terms'),
					['action' => 'index'],
					['escape' => false, 'class' => 'list-group-item list-group-item-action'],
				) ?>
				<?= $this->Html->link(
					'<i class="fas fa-language"></i> ' . __d('translate', 'List Translate Strings'),
					['controller' => 'TranslateStrings', 'action' => 'index'],
					['escape' => false, 'class' => 'list-group-item list-group-item-action'],
				) ?>
				<?= $this->Html->link(
					'<i class="fas fa-plus-circle"></i> ' . __d('translate', 'New Translate String'),
					['controller' => 'TranslateStrings', 'action' => 'add'],
					['escape' => false, 'class' => 'list-group-item list-group-item-action'],
				) ?>
			</div>
		</div>
	</nav>

	<!-- Main Content -->
	<div class="col-lg-9 col-md-8">
		<div class="page-header mb-4">
			<h1><i class="fas fa-edit"></i> <?= __d('translate', 'Edit Translate Term') ?></h1>
		</div>

		<!-- Edit Form Card -->
		<div class="card">
			<div class="card-body">
				<?= $this->Form->create($translateTerm) ?>
				<fieldset>
					<?php
					echo $this->Form->control('translate_string_id', ['options' => $translateStrings]);
					echo $this->Form->control('content');
					echo $this->Form->control('comment');
					echo $this->Form->control('translate_locale_id', ['options' => $translateLocales]);
					//echo $this->Form->control('user_id');
					//echo $this->Form->control('confirmed');
					//echo $this->Form->control('confirmed_by');
					?>
				</fieldset>
				<div class="mt-3">
					<div class="btn-group">
						<?= $this->Form->button(
							'<i class="fas fa-save"></i> ' . __d('translate', 'Submit'),
							['class' => 'btn btn-primary', 'escapeTitle' => false],
						) ?>
						<?= $this->Html->link(
							'<i class="fas fa-times"></i> ' . __d('translate', 'Cancel'),
							['action' => 'view', $translateTerm->id],
							['class' => 'btn btn-outline-secondary', 'escape' => false],
						) ?>
					</div>
				</div>
				<?= $this->Form->end() ?>
			</div>
		</div>
	</div>
</div>
