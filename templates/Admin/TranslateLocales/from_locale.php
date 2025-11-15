<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateLocale $translateLocale
 * @var mixed $existingLanguages
 * @var mixed $folders
 * @var mixed $path
 */
?>
<nav class="actions col-md-3 col-sm-4 col-12">
	<ul class="nav nav-pills flex-column">
		<li class="heading"><?= __d('translate', 'Actions') ?></li>
		<li><?= $this->Html->link(__d('translate', 'Overview'), ['controller' => 'Translate', 'action' => 'index']) ?></li>
		<li><?php echo $this->Html->link(__d('translate', 'List Translate Languages'), ['action' => 'index']);?></li>
	</ul>
</nav>
<div class="translateLocales index col-md-9 col-sm-8 col-12">
<h2><?php echo __d('translate', 'Import Translate Languages');?></h2>

<p>
	Looking into <code><?php echo h($path); ?></code>
</p>

<?php echo $this->Form->create(null);?>
	<fieldset>
		<legend><?php echo __dn('translate', '{0} locale found', '{0} locales found', count($folders), count($folders)); ?></legend>

	<ul class="list-unstyled">
	<?php
	foreach ($folders as $key => $folder) {
		if (array_key_exists($folder, $existingLanguages)) {
			echo '<li>' . $folder . '</li> (already exists)';
		} else {
			echo '<li>';
			echo $this->Form->control('language.' . $folder . '.confirm', ['type' => 'checkbox', 'label' => $folder]);
			echo $this->Form->control('language.' . $folder . '.name', ['label' => __d('translate', 'Language name')]);
			echo '</li>';
		}
	}
	?>
	</ul>
	</fieldset>
<?php echo $this->Form->submit(__d('translate', 'Submit'));
echo $this->Form->end();?>

</div>
