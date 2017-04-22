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

<h3>Dumping</h3>

Files are stored in
<code>...<?php echo substr(APP, -20) . 'Locale/' ;?> + <b>{locale}</b> + <?php echo '/'?> + <b>{domain}</b>.po</code>


<?php echo $this->Form->create(null);?>

	<fieldset>
		<legend><?php echo __('Languages and domains');?></legend>

	<?php
		echo $this->Form->input('domains', ['multiple'=>'checkbox', 'label' => __('Selection'), 'options' => $map]);
	?>
	</fieldset>

<?php echo $this->Form->submit(__('Submit')); echo $this->Form->end();?>
</div>
