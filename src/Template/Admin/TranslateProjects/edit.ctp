<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateProject $translateProject
 */
?><nav class="col-sm-4 col-xs-12">
	<ul class="side-nav nav nav-pills nav-stacked">
		<li class="heading"><?= __d('translate', 'Actions') ?></li>
		<li><?= $this->Form->postLink(
				__d('translate', 'Delete'),
				['action' => 'delete', $translateProject->id],
				['confirm' => __d('translate', 'Are you sure you want to delete # {0}?', $translateProject->id)]
			)
		?></li>
		<li><?= $this->Html->link(__d('translate', 'List Translate Projects'), ['action' => 'index']) ?></li>
		<li><?= $this->Html->link(__d('translate', 'List Translate Domains'), ['controller' => 'TranslateDomains', 'action' => 'index']) ?></li>
		<li><?= $this->Html->link(__d('translate', 'New Translate Domain'), ['controller' => 'TranslateDomains', 'action' => 'add']) ?></li>
	</ul>
</nav>
<div class="translateProjects form col-sm-8 col-xs-12">
	<?= $this->Form->create($translateProject) ?>
	<fieldset>
		<legend><?= __d('translate', 'Edit Translate Project') ?></legend>
		<?php
			echo $this->Form->input('name');
			echo $this->Form->input('type', ['options' => $translateProject::types()]);
			echo $this->Form->input('default');
			echo $this->Form->input('status', ['options' => $translateProject::statuses()]);
		?>
	</fieldset>
	<?= $this->Form->button(__d('translate', 'Submit')) ?>
	<?= $this->Form->end() ?>
</div>
