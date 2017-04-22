<?php
/**
 * @var \App\View\AppView $this
 */
?>
<div class="page form">
<h2><?php echo __('Reset {0}', __('Translate Project')); ?></h2>

<?php echo $this->Form->create(null);?>
	<fieldset>
		<legend><?php echo __('Reset {0}', __('Translate Project')); ?></legend>
	<?php
		echo $this->Form->input('reset', ['multiple'=>'checkbox', 'options'=>$removeOptions]);
	?>

	<?php
		echo $this->Form->input('language', ['multiple'=>'checkbox', 'options'=>$languages, 'after'=>__('only relevant for resetting terms')]);
	?>
	</fieldset>
<?php echo $this->Form->submit(__('Submit')); echo $this->Form->end();?>
</div>

<div class="actions">
	<ul>

		<li><?php echo $this->Html->link(__('List {0}', __('Translate Projects')), ['action' => 'index']);?></li>
	</ul>
</div>
