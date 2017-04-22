<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateLanguage $translateLanguage
 */
?>
<nav class="col-sm-4 col-xs-12">
	<ul class="side-nav nav nav-pills nav-stacked">
		<li class="heading"><?= __d('translate', 'Actions') ?></li>
		<li><?= $this->Html->link(__d('translate', 'List Translate Languages'), ['action' => 'index']) ?></li>
	</ul>
</nav>
<div class="translateLanguages form col-sm-8 col-xs-12">
	<?= $this->Form->create($translateLanguage) ?>
	<fieldset>
		<legend><?= __d('translate', 'Add Translate Language') ?></legend>
		<?php
			if (!empty($Languages)) {
				echo $this->Form->input('language_id', ['empty'=>true]);
			} else {
				echo $this->Form->input('iso2');
			}

			echo $this->Form->input('name');
			echo $this->Form->input('locale');
			echo $this->Form->input('active');
		?>
	</fieldset>
	<?= $this->Form->button(__d('translate', 'Submit')) ?>
	<?= $this->Form->end() ?>
</div>
