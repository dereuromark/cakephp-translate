<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateDomain $translateDomain
 */
?>
<div class="page form">
<?php echo $this->Form->create($translateDomain);?>
	<fieldset>
		<legend><?php echo __d('translate', 'Setup Default Translate Groups');?></legend>

	<?php
	$count = 0;
	foreach ($groups as $key => $group) {
		echo '<h3>'.$key.'</h3>';
		echo '<ul>';
		foreach ($group as $subgroup) {

			if (in_array($subgroup, $existingGroups)) {
				echo '<li>'.$subgroup.'</li> (already exists)';
			} else {
			echo '<li>'.$this->Form->input('TranslateDomain.'.$count.'.confirm', ['type'=>'checkbox','label'=>$subgroup]).''.$this->Form->input('TranslateDomain.'.$count.'.name', ['type'=>'hidden','value'=>$subgroup]).'</li>';
			}
			$count++;
		}
		echo '</ul>';
	}
	?>
	</fieldset>
<?php echo $this->Form->submit(__d('translate', 'Submit')); echo $this->Form->end();?>
</div>
<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(__d('translate', 'List Translate Groups'), ['action'=>'index']);?></li>
	</ul>
</div>
