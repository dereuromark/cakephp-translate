<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateGroup $translateGroup
 */
?>
<nav class="col-sm-4 col-xs-12">
	<ul class="side-nav nav nav-pills nav-stacked">
		<li class="heading"><?= __('Actions') ?></li>
		<li><?= $this->Html->link(__('Overview'), ['action' => 'index']) ?></li>
	</ul>
</nav>
<div class="translateStrings form col-sm-8 col-xs-12">
<?php echo $this->Form->create(null);?>
	<fieldset>
		<legend><?php echo __('Reset');?></legend>
	<?php
		$resetOptions = [
			'terms' => 'terms',
			'strings' => 'strings',
			'groups' => 'groups',
			'languages' => 'languages',
		];
		echo $this->Form->input('Form.sel', ['multiple'=>'checkbox', 'label' => __('Selection'), 'options' => $resetOptions]);

	?>
	</fieldset>

<?php echo $this->Form->submit(__('Reset')); echo $this->Form->end();?>
</div>
