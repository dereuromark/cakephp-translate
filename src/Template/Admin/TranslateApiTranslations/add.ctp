<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateApiTranslation $translateApiTranslation
 */
?>
<nav class="col-sm-4 col-xs-12">
	<ul class="side-nav nav nav-pills nav-stacked">
		<li class="heading"><?= __('Actions') ?></li>
		<li><?= $this->Html->link(__('List Translate Api Translations'), ['action' => 'index']) ?></li>
	</ul>
</nav>
<div class="translateApiTranslations form col-md-9 col-sm-8 col-xs-12">
	<?= $this->Form->create($translateApiTranslation) ?>
	<fieldset>
		<legend><?= __('Add Translate Api Translation') ?></legend>
		<?php
			echo $this->Form->input('key');
			echo $this->Form->input('value');
			echo $this->Form->input('from');
			echo $this->Form->input('to');
			echo $this->Form->input('engine');
		?>
	</fieldset>
	<?= $this->Form->button(__('Submit')) ?>
	<?= $this->Form->end() ?>
</div>
