<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateString $translateString
 */
?>
<nav class="col-sm-4 col-xs-12">
	<ul class="side-nav nav nav-pills nav-stacked">
		<li class="heading"><?= __d('translate', 'Actions') ?></li>
		<li><?= $this->Html->link(__d('translate', 'List Translate Strings'), ['action' => 'index']) ?></li>
	</ul>
</nav>
<div class="translateStrings form col-sm-8 col-xs-12">
	<?= $this->Form->create($translateString) ?>
	<fieldset>
		<legend><?= __d('translate', 'Add Translate String') ?></legend>
		<?php
			echo $this->Form->input('translate_group_id');

			echo $this->Form->input('name');
			echo $this->Form->input('plural');
			echo $this->Form->input('context');
			//echo $this->Form->input('comments');
			//echo $this->Form->input('references');
			//echo $this->Form->input('user_id');
			echo $this->Form->input('is_html');


			echo $this->Form->input('translate_afterwards', ['type'=>'checkbox']);
		?>
	</fieldset>
	<?= $this->Form->button(__d('translate', 'Submit')) ?>
	<?= $this->Form->end() ?>
</div>
