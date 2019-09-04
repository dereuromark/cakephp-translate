<?php
/**
 * @var \App\View\AppView $this
 * @var mixed $poFiles
 * @var mixed $potFiles
 */
?>
<div class="page form">
<?php echo $this->Form->create(null);?>
	<fieldset>
		<legend><?php echo __d('translate', 'From POT File');?></legend>
	<?php

		echo $this->Form->control('sel_pot', ['multiple'=>'checkbox', 'label'=>'Auswahl', 'options'=>$potFiles]);

		//echo $this->Form->control('active');
	?>
	</fieldset>

	<fieldset>
		<legend><?php echo __d('translate', 'From PO File');?></legend>

	<?php
		echo $this->Form->control('sel_po', ['multiple'=>'checkbox', 'label'=>'Auswahl', 'options'=>$poFiles]);
	?>
	</fieldset>

	<fieldset>
		<legend><?php echo __d('translate', 'From Source Code');?></legend>
	<?php
		echo $this->Form->control('source_code', ['type'=>'checkbox']);
	?>
	</fieldset>

	<fieldset>
		<legend><?php echo __d('translate', 'Controller Names');?></legend>
	<?php
		echo $this->Form->control('controller_names', ['type'=>'checkbox']);
	?>
	</fieldset>

<?php echo $this->Form->submit(__d('translate', 'Submit')); echo $this->Form->end();?>
</div>
<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(__d('translate', 'List Translate Domains'), ['action'=>'index']);?></li>
	</ul>
</div>
