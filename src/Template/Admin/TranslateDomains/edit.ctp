<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateDomain $translateDomain
 */
?><nav class="col-md-3 col-sm-4 col-xs-12">
	<ul class="side-nav nav nav-pills nav-stacked">
		<li class="heading"><?= __d('translate', 'Actions') ?></li>
		<li><?= $this->Form->postLink(
				__d('translate', 'Delete'),
				['action' => 'delete', $translateDomain->id],
				['confirm' => __d('translate', 'Are you sure you want to delete # {0}?', $translateDomain->id)]
			)
		?></li>
		<li><?= $this->Html->link(__d('translate', 'List Translate Domains'), ['action' => 'index']) ?></li>
		<li><?= $this->Html->link(__d('translate', 'List Translate Projects'), ['controller' => 'TranslateProjects', 'action' => 'index']) ?></li>
		<li><?= $this->Html->link(__d('translate', 'New Translate Project'), ['controller' => 'TranslateProjects', 'action' => 'add']) ?></li>
		<li><?= $this->Html->link(__d('translate', 'List Translate Strings'), ['controller' => 'TranslateStrings', 'action' => 'index']) ?></li>
		<li><?= $this->Html->link(__d('translate', 'New Translate String'), ['controller' => 'TranslateStrings', 'action' => 'add']) ?></li>
	</ul>
</nav>
<div class="translateDomains form col-md-9 col-sm-8 col-xs-12">
	<?= $this->Form->create($translateDomain) ?>
	<fieldset>
		<legend><?= __d('translate', 'Edit Translate Domain') ?></legend>
		<?php
			echo $this->Form->control('name');
			echo $this->Form->control('active');

			echo $this->Form->control('path');
		?>
	</fieldset>
	<?= $this->Form->button(__d('translate', 'Submit')) ?>
	<?= $this->Form->end() ?>
</div>
