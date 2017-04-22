<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateGroup $translateGroup
 */
?><nav class="col-sm-4 col-xs-12">
	<ul class="side-nav nav nav-pills nav-stacked">
		<li class="heading"><?= __('Actions') ?></li>
		<li><?= $this->Form->postLink(
				__('Delete'),
				['action' => 'delete', $translateGroup->id],
				['confirm' => __('Are you sure you want to delete # {0}?', $translateGroup->id)]
			)
		?></li>
		<li><?= $this->Html->link(__('List Translate Groups'), ['action' => 'index']) ?></li>
		<li><?= $this->Html->link(__('List Translate Projects'), ['controller' => 'TranslateProjects', 'action' => 'index']) ?></li>
		<li><?= $this->Html->link(__('New Translate Project'), ['controller' => 'TranslateProjects', 'action' => 'add']) ?></li>
		<li><?= $this->Html->link(__('List Translate Strings'), ['controller' => 'TranslateStrings', 'action' => 'index']) ?></li>
		<li><?= $this->Html->link(__('New Translate String'), ['controller' => 'TranslateStrings', 'action' => 'add']) ?></li>
	</ul>
</nav>
<div class="translateGroups form col-sm-8 col-xs-12">
	<?= $this->Form->create($translateGroup) ?>
	<fieldset>
		<legend><?= __('Edit Translate Group') ?></legend>
		<?php
			echo $this->Form->input('name');
			echo $this->Form->input('active');
		?>
	</fieldset>
	<?= $this->Form->button(__('Submit')) ?>
	<?= $this->Form->end() ?>
</div>
