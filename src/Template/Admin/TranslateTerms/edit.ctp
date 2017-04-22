<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateTerm $translateTerm
 */
?><nav class="col-sm-4 col-xs-12">
	<ul class="side-nav nav nav-pills nav-stacked">
		<li class="heading"><?= __('Actions') ?></li>
		<li><?= $this->Form->postLink(
				__('Delete'),
				['action' => 'delete', $translateTerm->id],
				['confirm' => __('Are you sure you want to delete # {0}?', $translateTerm->id)]
			)
		?></li>
		<li><?= $this->Html->link(__('List Translate Strings'), ['controller' => 'TranslateStrings', 'action' => 'index']) ?></li>
		<li><?= $this->Html->link(__('New Translate String'), ['controller' => 'TranslateStrings', 'action' => 'add']) ?></li>
	</ul>
</nav>
<div class="translateTerms form col-sm-8 col-xs-12">
	<?= $this->Form->create($translateTerm) ?>
	<fieldset>
		<legend><?= __('Edit Translate Term') ?></legend>
		<?php
		echo $this->Form->input('translate_string_id', ['options' => $translateStrings]);
		echo $this->Form->input('content');
		echo $this->Form->input('comment');
		echo $this->Form->input('translate_language_id', ['options' => $translateLanguages]);
		//echo $this->Form->input('user_id');
		//echo $this->Form->input('confirmed');
		//echo $this->Form->input('confirmed_by');
		?>
	</fieldset>
	<?= $this->Form->button(__('Submit')) ?>
	<?= $this->Form->end() ?>
</div>
