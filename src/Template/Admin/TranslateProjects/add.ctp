<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateProject $translateProject
 */
?>
<nav class="col-sm-4 col-xs-12">
	<ul class="side-nav nav nav-pills nav-stacked">
		<li class="heading"><?= __('Actions') ?></li>
		<li><?= $this->Html->link(__('List Translate Projects'), ['action' => 'index']) ?></li>
	</ul>
</nav>
<div class="translateProjects form col-sm-8 col-xs-12">
	<?= $this->Form->create($translateProject) ?>
	<fieldset>
		<legend><?= __('Add Translate Project') ?></legend>
		<?php
			echo $this->Form->input('name');
			echo $this->Form->input('type', ['options' => $translateProject::types()]);
			echo $this->Form->input('default');
			echo $this->Form->input('status', ['options' => $translateProject::statuses()]);
		?>
	</fieldset>
	<?= $this->Form->button(__('Submit')) ?>
	<?= $this->Form->end() ?>
</div>
