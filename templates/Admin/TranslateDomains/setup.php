<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateDomain $translateDomain
 * @var mixed $existingDomains
 * @var mixed $domains
 */
?>
<div class="page form">
<?php echo $this->Form->create($translateDomain);?>
	<fieldset>
		<legend><?php echo __d('translate', 'Setup Default Translate Domains');?></legend>

	<?php
	$count = 0;
	foreach ($domains as $key => $domainGroup) {
		echo '<h3>' . $key . '</h3>';
		echo '<ul>';
		foreach ($domainGroup as $domainName) {

			if (in_array($domainName, $existingDomains, true)) {
				echo '<li>' . $domainName . '</li> (already exists)';
			} else {
				echo '<li>' . $this->Form->control('TranslateDomain.' . $count . '.confirm', ['type' => 'checkbox', 'label' => $domainName]) . '' . $this->Form->control('TranslateDomain.' . $count . '.name', ['type' => 'hidden', 'value' => $domainName]) . '</li>';
			}
			$count++;
		}
		echo '</ul>';
	}
	?>
	</fieldset>
<?php echo $this->Form->submit(__d('translate', 'Submit'));
echo $this->Form->end();?>
</div>
<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(__d('translate', 'List Translate Domains'), ['action' => 'index']);?></li>
	</ul>
</div>
