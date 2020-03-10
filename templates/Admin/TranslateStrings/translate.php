<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateLanguage[]|\Cake\Collection\CollectionInterface $translateLanguages
 * @var \Translate\Model\Entity\TranslateString $translateString
 * @var array $suggestions
 */

?>
<nav class="actions col-md-3 col-sm-4 col-xs-12">
	<ul class="side-nav nav nav-pills nav-stacked">
		<li class="heading"><?= __d('translate', 'Actions') ?></li>
		<li><?= $this->Html->link(__d('translate', 'Overview'), ['controller' => 'Translate', 'action' => 'index']) ?></li>
		<li><?= $this->Html->link(__d('translate', 'List Translate Strings'), ['controller' => 'TranslateStrings', 'action' => 'index', '?' => $this->request->query]) ?></li>
		<li><?php echo $this->Html->link(__d('translate', 'Edit Translate String'), ['action'=>'edit', $translateString['id']]);?></li>
	</ul>
</nav>
<div class="translateStrings index col-md-9 col-sm-8 col-xs-12">

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
		//echo $this->Form->control('id');

	if ($translateString->plural) {
		foreach ($translateLanguages as $translateLanguage) {
			$key = $translateLanguage['iso2'];
			echo $this->Form->control('content_'.$key, ['type'=>'text', 'label'=> __d('translate', 'Singular'). ' ' . $translateLanguage['iso2'], 'rel'=>$key]);
			if (!empty($suggestions[$key])) {
				echo $this->element('suggestions', ['suggestions' => $suggestions[$key], 'target' => 'content-' . $key]);
			}
		}

		foreach ($translateLanguages as $translateLanguage) {
			$key = $translateLanguage['iso2'];
			//TODO add plural 3 to 6 if necessary
			echo $this->Form->control('plural_2_'.$key, ['type'=>'text', 'label'=>__d('translate', 'Plural') . ' ' . $translateLanguage['iso2'], 'rel' => 'p' . $key]);

		}

	} else {

		foreach ($translateLanguages as $translateLanguage) {
			$key = $translateLanguage['iso2'];
			echo $this->Form->control('content_'.$key, ['type'=>'textarea','label'=>h($translateLanguage['name']), 'rel'=>$key]);
			if (!empty($suggestions[$key])) {
				echo $this->element('suggestions', ['suggestions' => $suggestions[$key], 'key' => $key]);
			}
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
$sep = explode(PHP_EOL, $translateString['references']);
$references = [];
foreach ($sep as $s) {
	$s = trim($s);
	if ($s !== '') {
		$references[] = $s;
	}
}

?>

<h3>Additional Infos</h3>
Group: <?php echo $this->Html->link($translateString->translate_domain->name, ['action' => 'index', '?' => ['translate_domain_id' => $translateString->translate_domain_id]]); ?><br/>

References: <?php echo count($references)?>x
	<?php if ($references) { ?>
	<ul class="references">
		<?php foreach ($references as $key => $reference) { ?>
		<?php if ($this->Translation->canDisplayReference($translateString->translate_domain)) { ?>
			<li><?php echo $this->Html->link($reference, ['action' => 'displayReference', $translateString->id, $key], ['target'=> '_blank', 'data-toggle'=> "modal", 'data-target' => ".bs-example-modal-lg"]); ?></li>
		<?php } else { ?>
			<li><?php echo h($reference); ?></li>
		<?php } ?>
	<?php } ?>
	</ul>
	<?php } ?>

</div>


<div class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			...
			dsfsdfdsf
			<br>
			sdfsdfdsf
		</div>
	</div>
</div>

<?php $this->append('script'); ?>
<script>
	$(function() {
		$('#myLargeModalLabel').modal();

		$('ul.references a').on('click', function (event) {
		    event.preventDefault();

			modal = $('#myLargeModalLabel').modal();
			modal.show();
		})
	});
</script>
<?php $this->end(); ?>
