<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateString $translateString
 */
?><nav class="col-sm-4 col-xs-12">
	<ul class="side-nav nav nav-pills nav-stacked">
		<li class="heading"><?= __('Actions') ?></li>
		<li><?= $this->Form->postLink(
				__('Delete'),
				['action' => 'delete', $translateString->id],
				['confirm' => __('Are you sure you want to delete # {0}?', $translateString->id)]
			)
		?></li>
		<li><?= $this->Html->link(__('List Translate Strings'), ['action' => 'index']) ?></li>
	</ul>
</nav>
<div class="translateStrings form col-sm-8 col-xs-12">
	<?= $this->Form->create($translateString) ?>
	<fieldset>
		<legend><?= __('Edit Translate String') ?></legend>
		<?php
		//echo $this->Form->input('id');
		echo $this->Form->input('translate_group_id');

		echo $this->Form->input('name');
		echo $this->Form->input('plural');
		echo $this->Form->input('context');
		//echo $this->Form->input('comments');
		//echo $this->Form->input('flags');
		//echo $this->Form->input('occurrences');
		//echo $this->Form->input('user_id');
		echo $this->Form->input('is_html');


		echo $this->Form->input('translate_afterwards', ['type'=>'checkbox']);

		//echo $this->Form->input('translate_groups._ids', ['options' => $translateGroups]);
		?>
	</fieldset>
	<?= $this->Form->button(__('Submit')) ?>
	<?= $this->Form->end() ?>
</div>
