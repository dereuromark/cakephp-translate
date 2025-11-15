<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateLocale $translateLocale
 * @var mixed $existingFolders
 * @var mixed $languages
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
<h2><?php echo __d('translate', 'Export Translate Languages');?></h2>

	<p>
		Checking <code><?php echo h($path); ?></code>
	</p>

<?php echo $this->Form->create(null);?>
	<fieldset>
		<legend><?php echo __dn('translate', '{0} locale', '{0} locales', count($languages), count($languages)); ?></legend>
	<div>
	<?php
	foreach ($languages as $key => $language) {
		if (in_array($key, $existingFolders, true)) {
			echo '<p><b>' . $key . '</b>' . ' - ' . $language . ' (already exists)</p>';
		} else {
			echo $this->Form->control('locale.' . $key . '.confirm', ['type' => 'checkbox', 'label' => $key . ' (' . $language . ')']);
		}
	}
	?>
	</div>
	<?php

	?>
	</fieldset>
<?php echo $this->Form->submit(__d('translate', 'Submit'));
echo $this->Form->end();?>

</div>
