<?php
/**
 * @var \App\View\AppView $this
 */
?>
<div class="page form">
<h2><?php echo __d('translate', 'Reset {0}', __d('translate', 'Translate Project')); ?></h2>

<?php echo $this->Form->create(null);?>
	<fieldset>
		<legend><?php echo __d('translate', 'Reset {0}', __d('translate', 'Translate Project')); ?></legend>
	<?php
		echo $this->Form->control('reset', ['multiple'=>'checkbox', 'options'=>$removeOptions]);
	?>

	<?php
		echo $this->Form->control('language', ['multiple'=>'checkbox', 'options'=>$languages, 'after'=>__d('translate', 'only relevant for resetting terms')]);
	?>
	</fieldset>
<?php echo $this->Form->submit(__d('translate', 'Submit')); echo $this->Form->end();?>
</div>

<div class="actions">
	<ul>

		<li><?php echo $this->Html->link(__d('translate', 'List {0}', __d('translate', 'Translate Projects')), ['action' => 'index']);?></li>
	</ul>
</div>
