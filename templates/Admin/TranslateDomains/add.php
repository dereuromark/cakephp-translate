<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateDomain $translateDomain
 */
?>
<nav class="col-md-3 col-sm-4 col-12">
	<ul class="nav nav-pills flex-column">
		<li class="heading"><?= __d('translate', 'Actions') ?></li>
		<li><?= $this->Html->link(__d('translate', 'List Translate Domains'), ['action' => 'index']) ?></li>
		<li><?= $this->Html->link(__d('translate', 'List Translate Projects'), ['controller' => 'TranslateProjects', 'action' => 'index']) ?></li>
		<li><?= $this->Html->link(__d('translate', 'New Translate Project'), ['controller' => 'TranslateProjects', 'action' => 'add']) ?></li>
		<li><?= $this->Html->link(__d('translate', 'List Translate Strings'), ['controller' => 'TranslateStrings', 'action' => 'index']) ?></li>
		<li><?= $this->Html->link(__d('translate', 'New Translate String'), ['controller' => 'TranslateStrings', 'action' => 'add']) ?></li>
	</ul>
</nav>
<div class="translateDomains form col-md-9 col-sm-8 col-12">
	<?= $this->Form->create($translateDomain) ?>
	<fieldset>
		<legend><?= __d('translate', 'Add Translate Domain') ?></legend>
		<?php
			echo $this->Form->control('name');
			echo $this->Form->control('active');

			echo $this->Form->control('path');
		?>
	</fieldset>
	<?= $this->Form->button(__d('translate', 'Submit')) ?>
	<?= $this->Form->end() ?>
</div>
