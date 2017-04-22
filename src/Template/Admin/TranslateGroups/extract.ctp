<?php
/**
 * @var \App\View\AppView $this
 */
?>
<div class="page form">
<?php echo $this->Form->create(null);?>
	<fieldset>
		<legend><?php echo __('From POT File');?></legend>
	<?php

		echo $this->Form->input('sel_pot', ['multiple'=>'checkbox', 'label'=>'Auswahl', 'options'=>$potFiles]);

		//echo $this->Form->input('active');
	?>
	</fieldset>

	<fieldset>
		<legend><?php echo __('From PO File');?></legend>

	<?php
		echo $this->Form->input('sel_po', ['multiple'=>'checkbox', 'label'=>'Auswahl', 'options'=>$poFiles]);
	?>
	</fieldset>

	<fieldset>
		<legend><?php echo __('From Source Code');?></legend>
	<?php
		echo $this->Form->input('source_code', ['type'=>'checkbox']);
	?>
	</fieldset>

	<fieldset>
		<legend><?php echo __('Controller Names');?></legend>
	<?php
		echo $this->Form->input('controller_names', ['type'=>'checkbox']);
	?>
	</fieldset>

<?php echo $this->Form->submit(__('Submit')); echo $this->Form->end();?>
</div>
<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(__('List Translate Groups'), ['action'=>'index']);?></li>
	</ul>
</div>
