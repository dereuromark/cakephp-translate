<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateLanguage $translateLanguage
 * @var mixed $Languages
 */
?><nav class="col-md-3 col-sm-4 col-12">
	<ul class="nav nav-pills flex-column">
		<li class="heading"><?= __d('translate', 'Actions') ?></li>
		<li><?= $this->Form->postLink(
			__d('translate', 'Delete'),
			['action' => 'delete', $translateLanguage->id],
			['confirm' => __d('translate', 'Are you sure you want to delete # {0}?', $translateLanguage->id)],
		)
							?></li>
		<li><?= $this->Html->link(__d('translate', 'List Translate Languages'), ['action' => 'index']) ?></li>
	</ul>
</nav>
<div class="translateLanguages form col-md-9 col-sm-8 col-12">
	<?= $this->Form->create($translateLanguage) ?>
	<fieldset>
		<legend><?= __d('translate', 'Edit Translate Language') ?></legend>
		<?php
		echo $this->Form->control('name');
		echo $this->Form->control('locale');

		if (!empty($Languages)) {
			echo $this->Form->control('language_id', ['empty' => true]);
		} else {
			echo $this->Form->control('iso2');
		}

		echo $this->Form->control('active');
		?>
	</fieldset>
	<?= $this->Form->button(__d('translate', 'Submit')) ?>
	<?= $this->Form->end() ?>
</div>
