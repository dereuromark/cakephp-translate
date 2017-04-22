<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateLanguage $translateLanguage
 */
?>
<div class="col-md-12">
<h2><?php echo __d('translate', 'Export Translate Languages');?></h2>
<?php echo count($existingFolders)?> locale(s) gefunden: <?php echo implode(', ', $existingFolders);?>

<?php echo $this->Form->create(null);?>
	<fieldset>
		<legend><?php echo __d('translate', 'Create');?></legend>
	<ul>
	<?php
	$count = 0;
	foreach ($languages as $key => $language) {
		if (in_array($key, $existingFolders)) {
			echo '<li>'.$language.'</li> (already exists)';
		} else {

		echo $this->Form->input('TranslateLanguage.'.$count.'.folder', ['type'=>'hidden','value'=>$key]);
		echo '<li>'.$this->Form->input('TranslateLanguage.'.$count.'.confirm', ['type'=>'checkbox','label'=>$language]).'</li>';
		$count++;
		}
	}
	?>
	</ul>
	<?php

	?>
	</fieldset>
<?php echo $this->Form->submit(__d('translate', 'Submit')); echo $this->Form->end();?>

</div>

<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(__d('translate', 'List Translate Languages'), ['action'=>'index']);?></li>
	</ul>
</div>
