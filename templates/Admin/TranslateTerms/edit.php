<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateTerm $translateTerm
 * @var mixed $translateLanguages
 * @var mixed $translateStrings
 */
?><nav class="col-md-3 col-sm-4 col-xs-12">
	<ul class="side-nav nav nav-pills nav-stacked">
		<li class="heading"><?= __d('translate', 'Actions') ?></li>
		<li><?= $this->Form->postLink(
			__d('translate', 'Delete'),
			['action' => 'delete', $translateTerm->id],
			['confirm' => __d('translate', 'Are you sure you want to delete # {0}?', $translateTerm->id)],
		)
							?></li>
		<li><?= $this->Html->link(__d('translate', 'List Translate Strings'), ['controller' => 'TranslateStrings', 'action' => 'index']) ?></li>
		<li><?= $this->Html->link(__d('translate', 'New Translate String'), ['controller' => 'TranslateStrings', 'action' => 'add']) ?></li>
	</ul>
</nav>
<div class="translateTerms form col-md-9 col-sm-8 col-xs-12">
	<?= $this->Form->create($translateTerm) ?>
	<fieldset>
		<legend><?= __d('translate', 'Edit Translate Term') ?></legend>
		<?php
		echo $this->Form->control('translate_string_id', ['options' => $translateStrings]);
		echo $this->Form->control('content');
		echo $this->Form->control('comment');
		echo $this->Form->control('translate_language_id', ['options' => $translateLanguages]);
		//echo $this->Form->control('user_id');
		//echo $this->Form->control('confirmed');
		//echo $this->Form->control('confirmed_by');
		?>
	</fieldset>
	<?= $this->Form->button(__d('translate', 'Submit')) ?>
	<?= $this->Form->end() ?>
</div>
