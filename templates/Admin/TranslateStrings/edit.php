<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateString $translateString
 */
?><nav class="col-md-3 col-sm-4 col-xs-12">
	<ul class="side-nav nav nav-pills nav-stacked">
		<li class="heading"><?= __d('translate', 'Actions') ?></li>
		<li><?= $this->Form->postLink(
				__d('translate', 'Delete'),
				['action' => 'delete', $translateString->id],
				['confirm' => __d('translate', 'Are you sure you want to delete # {0}?', $translateString->id)]
			)
		?></li>
		<li><?= $this->Html->link(__d('translate', 'List Translate Strings'), ['action' => 'index']) ?></li>
	</ul>
</nav>
<div class="translateStrings form col-md-9 col-sm-8 col-xs-12">
	<?= $this->Form->create($translateString) ?>
	<fieldset>
		<legend><?= __d('translate', 'Edit Translate String') ?></legend>
		<?php
		//echo $this->Form->control('id');
		echo $this->Form->control('translate_domain_id');

		echo $this->Form->control('name');
		echo $this->Form->control('plural');
		echo $this->Form->control('context');
		//echo $this->Form->control('comments');
		//echo $this->Form->control('flags');
		//echo $this->Form->control('references');
		//echo $this->Form->control('user_id');
		echo $this->Form->control('is_html');


		echo $this->Form->control('translate_afterwards', ['type'=>'checkbox']);

		//echo $this->Form->control('translate_domains._ids', ['options' => $translateDomains]);
		?>
	</fieldset>
	<?= $this->Form->button(__d('translate', 'Submit')) ?>
	<?= $this->Form->end() ?>
</div>
