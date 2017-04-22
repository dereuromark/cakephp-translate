<?php
/**
 * @var \App\View\AppView $this
 */
?>

<nav class="col-sm-4 col-xs-12">
	<ul class="side-nav nav nav-pills nav-stacked">
		<li class="heading"><?= __('Actions') ?></li>
		<li><?php echo $this->Html->link(__('List Translate Strings'), ['action'=>'index']);?></li>
	</ul>
</nav>

<div class="page form col-sm-8 col-xs-12">
<?php echo $this->Form->create(null);?>
	<fieldset>
		<legend><?php echo __('From POT File');?></legend>
	<?php

		echo $this->Form->input('sel_pot', ['multiple'=>'checkbox', 'label' => __('Selection'), 'options' => $potFiles]);

		//echo $this->Form->input('active');
	?>
	</fieldset>

	<fieldset>
		<legend><?php echo __('From PO File');?></legend>

	<?php
		echo $this->Form->input('sel_po', ['multiple'=>'checkbox', 'label' => __('Selection'), 'options' => $poFiles]);
	?>
	</fieldset>

<?php echo $this->Form->submit(__('Submit')); echo $this->Form->end();?>
</div>
