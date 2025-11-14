<?php
/**
 * @var \App\View\AppView $this
 * @var mixed $poFiles
 * @var mixed $potFiles
 */
?>

<nav class="col-md-3 col-sm-4 col-xs-12">
	<ul class="side-nav nav nav-pills nav-stacked">
		<li class="heading"><?= __d('translate', 'Actions') ?></li>
		<li><?php echo $this->Html->link(__d('translate', 'List Translate Strings'), ['action' => 'index']);?></li>
	</ul>
</nav>

<div class="page form col-md-9 col-sm-8 col-xs-12">
<?php echo $this->Form->create(null);?>
	<fieldset>
		<legend><?php echo __d('translate', 'From POT File');?></legend>
	<?php

		echo $this->Form->control('sel_pot', ['multiple' => 'checkbox', 'label' => __d('translate', 'Selection'), 'options' => $potFiles]);

		//echo $this->Form->control('active');
	?>
	</fieldset>

	<fieldset>
		<legend><?php echo __d('translate', 'From PO File');?></legend>

	<?php
		echo $this->Form->control('sel_po', ['multiple' => 'checkbox', 'label' => __d('translate', 'Selection'), 'options' => $poFiles]);
	?>
	</fieldset>

<?php echo $this->Form->submit(__d('translate', 'Submit'));
echo $this->Form->end();?>
</div>
