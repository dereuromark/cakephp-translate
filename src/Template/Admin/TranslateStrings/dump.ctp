<?php
/**
 * @var \App\View\AppView $this
 */
?>

<nav class="col-md-3 col-sm-4 col-xs-12">
	<ul class="side-nav nav nav-pills nav-stacked">
		<li class="heading"><?= __d('translate', 'Actions') ?></li>
		<li><?php echo $this->Html->link(__d('translate', 'List Translate Strings'), ['action'=>'index']);?></li>
	</ul>
</nav>

<div class="page form col-md-9 col-sm-8 col-xs-12">

<h3>Dumping</h3>

Files are stored in
<code>...<?php echo substr(APP, -20) . 'Locale/' ;?> + <b>{locale}</b> + <?php echo '/'?> + <b>{domain}</b>.po</code>


<?php echo $this->Form->create(null);?>

	<fieldset>
		<legend><?php echo __d('translate', 'Languages and domains');?></legend>

	<?php
		if (empty($map)) {
			echo '<i>' . __d('translate', 'No active domains found. Please activate them if they already exist.') . '</i>';
		} else {
			echo $this->Form->control('domains', ['multiple'=>'checkbox', 'label' => __d('translate', 'Selection'), 'options' => $map]);
		}
	?>
	</fieldset>

<?php echo $this->Form->submit(__d('translate', 'Submit')); echo $this->Form->end();?>
</div>
