<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateApiTranslation $translateApiTranslation
 */
?><nav class="col-md-3 col-sm-4 col-12">
	<ul class="nav nav-pills flex-column">
		<li class="heading"><?= __('Actions') ?></li>
		<li><?= $this->Form->postLink(
			__('Delete'),
			['action' => 'delete', $translateApiTranslation->id],
			['confirm' => __('Are you sure you want to delete # {0}?', $translateApiTranslation->id)],
		)
							?></li>
		<li><?= $this->Html->link(__('List Translate Api Translations'), ['action' => 'index']) ?></li>
	</ul>
</nav>
<div class="translateApiTranslations form col-md-9 col-sm-8 col-12">
	<?= $this->Form->create($translateApiTranslation) ?>
	<fieldset>
		<legend><?= __('Edit Translate Api Translation') ?></legend>
		<?php
			echo $this->Form->control('key');
			echo $this->Form->control('value');
			echo $this->Form->control('from');
			echo $this->Form->control('to');
			echo $this->Form->control('engine');
		?>
	</fieldset>
	<?= $this->Form->button(__('Submit')) ?>
	<?= $this->Form->end() ?>
</div>
