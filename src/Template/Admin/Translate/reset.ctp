<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateDomain $translateDomain
 */
?>
<nav class="col-md-3 col-sm-4 col-xs-12">
	<ul class="side-nav nav nav-pills nav-stacked">
		<li class="heading"><?= __d('translate', 'Actions') ?></li>
		<li><?= $this->Html->link(__d('translate', 'Overview'), ['action' => 'index']) ?></li>
	</ul>
</nav>
<div class="translateStrings form col-md-9 col-sm-8 col-xs-12">
<?php echo $this->Form->create(null);?>
	<fieldset>
		<legend><?php echo __d('translate', 'Reset');?></legend>
	<?php
		$resetOptions = [
			'terms' => 'terms',
			'strings' => 'strings',
			'groups' => 'groups',
			'languages' => 'languages',
		];
		echo $this->Form->control('Form.sel', ['multiple'=>'checkbox', 'label' => __d('translate', 'Selection'), 'options' => $resetOptions]);

	?>
	</fieldset>

<?php echo $this->Form->submit(__d('translate', 'Reset')); echo $this->Form->end();?>
</div>
