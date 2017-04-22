<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateLanguage[] $translateLanguages
 * @var \Translate\Model\Entity\TranslateString $translateString
 */

?>
<nav class="actions col-sm-4 col-xs-12">
	<ul class="side-nav nav nav-pills nav-stacked">
		<li class="heading"><?= __d('translate', 'Actions') ?></li>
		<li><?= $this->Html->link(__d('translate', 'Overview'), ['controller' => 'Translate', 'action' => 'index']) ?></li>
		<li><?= $this->Html->link(__d('translate', 'List Translate Strings'), ['controller' => 'TranslateStrings', 'action' => 'index', '?' => $this->request->query]) ?></li>
		<li><?php echo $this->Html->link(__d('translate', 'Edit Translate String'), ['action'=>'edit', $translateString['id']]);?></li>
	</ul>
</nav>
<div class="translateStrings index col-sm-8 col-xs-12">

<h3>String</h3>

<div style="float: right">
	<?= $this->Html->link($this->Format->icon('edit'), ['action' => 'edit', $translateString->id, '?' => ['translate_afterwards' => true]], ['escape' => false]); ?>
</div>
<code>
	 <?php echo h($translateString['name'])?>
</code>
	<?php if ($translateString->plural) { ?>
		/ <code>
			<?php echo h($translateString->plural)?>
		</code>
	<?php } ?>

<?php if ($translateString->is_html) { ?>
	<p>HTML (Manual escaping necessary!)</p>
<?php } ?>

<?php echo $this->Form->create($translateString);?>
	<fieldset>
		<legend><?php echo __d('translate', 'Translate This String');?></legend>

	<?php
		//echo $this->Form->input('id');

	if ($translateString->plural) {
		foreach ($translateLanguages as $translateLanguage) {
			$key = $translateLanguage['iso2'];
			echo $this->Form->input('content_'.$key, ['type'=>'text', 'label'=> __d('translate', 'Singular'). ' ' . $translateLanguage['iso2'], 'rel'=>$key]);
		}

		foreach ($translateLanguages as $translateLanguage) {
			$key = $translateLanguage['iso2'];
			//TODO add plural 3 to 6 if necessary
			echo $this->Form->input('plural_2_'.$key, ['type'=>'text', 'label'=>__d('translate', 'Plural') . ' ' . $translateLanguage['iso2'], 'rel' => 'p' . $key]);

		}

	} else {

		foreach ($translateLanguages as $translateLanguage) {
			$key = $translateLanguage['iso2'];
			echo $this->Form->input('content_'.$key, ['type'=>'textarea','label'=>h($translateLanguage['name']), 'rel'=>$key]);
		}
	}

	?>
	</fieldset>

	<div class="form-group buttons">
		<div class="col-md-offset-4 col-lg-offset-3 col-md-8 col-lg-9">
<?php echo $this->Form->button(__d('translate', 'Save'), ['name' => 'save', 'value' => 'Task']);?>

<?php echo $this->Form->button(__d('translate', 'Save').' + '.__d('translate', 'Next'), ['name' => 'next', 'value' => 'Task', 'class' => 'btn btn-success']);?>
<?php echo $this->Form->end();?>
		</div>
	</div>
<br/>
<?php
$sep = explode(PHP_EOL, $translateString['occurrences']);
$occ = [];

?>

<h3>Additional Infos</h3>
Group: <?php echo $this->Html->link($translateString->translate_group->name, ['action' => 'index', '?' => ['translate_group_id' => $translateString->translate_group_id]]); ?><br/>
Descr: <?php echo nl2br(h($translateString['description']))?><br/>
Occurrances: <?php echo count($occ)?>x / <?php echo nl2br(h($translateString['occurrences']))?>
<br/><br/>
<?php echo __d('translate', 'textExcerpt')?>: ..


</div>

