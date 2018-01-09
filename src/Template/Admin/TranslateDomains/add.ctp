<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateDomain $translateDomain
 */
?>
<nav class="col-sm-4 col-xs-12">
	<ul class="side-nav nav nav-pills nav-stacked">
		<li class="heading"><?= __d('translate', 'Actions') ?></li>
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
		<legend><?= __d('translate', 'Add Translate Domain') ?></legend>
		<?php
			echo $this->Form->input('name');
			echo $this->Form->input('active');

			echo $this->Form->input('path');
		?>
	</fieldset>
	<?= $this->Form->button(__d('translate', 'Submit')) ?>
	<?= $this->Form->end() ?>
</div>
