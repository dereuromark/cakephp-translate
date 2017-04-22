<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateLanguage $translateLanguage
 */
?>
<div class="col-md-12">
<h2><?php echo __d('translate', 'Import Translate Languages');?></h2>

<?php echo $this->Form->create(null);?>
	<fieldset>
		<legend><?php echo __d('translate', 'Find');?></legend>
	<?php echo count($folders)?> locale(s) gefunden
	<ul>
	<?php
	foreach ($folders as $key => $folder) {
		if (array_key_exists($folder, $existingLanguages)) {
			echo '<li>'.$folder.'</li> (already exists)';
		} else {
		echo $this->Form->input('TranslateLanguage.'.$key.'.locale', ['type'=>'hidden','value'=>$folder]);
		echo '<li>'.$this->Form->input('TranslateLanguage.'.$key.'.confirm', ['type'=>'checkbox','label'=>$folder]).''.$this->Form->input('TranslateLanguage.'.$key.'.name', ['label'=> __d('translate', 'languageName')]).'</li>';
		}
	}
	?>
	</ul>
	</fieldset>
<?php echo $this->Form->submit(__d('translate', 'Submit')); echo $this->Form->end();?>

</div>

<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(__d('translate', 'List Translate Languages'), ['action'=>'index']);?></li>
	</ul>
</div>
