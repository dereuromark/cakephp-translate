<?php
/**
 * @var \App\View\AppView $this
 */
?>

<nav class="col-sm-4 col-xs-12">
	<ul class="side-nav nav nav-pills nav-stacked">
		<li class="heading"><?= __d('translate', 'Actions') ?></li>
		<li><?= $this->Html->link(__d('translate', 'Overview'), ['action' => 'index']) ?></li>
	</ul>
</nav>
<div class="translateStrings form col-sm-8 col-xs-12">
<h2>Convert text</h2>

<?php if (!empty($text)) { ?>
<h3>Result</h3>
<?php
		echo $this->Form->input('text', ['value' => $text, 'class' => 'form-control', 'type'=>'textarea', 'rows' => 5]);
?>

<?php } ?>

<h3>Input</h3>

<?php echo $this->Form->create($translate);?>
	<fieldset>
		<legend><?php echo __d('translate', 'Convert text');?></legend>
	<?php
		echo $this->Form->input('direction', ['type'=>'select', 'options' => ['From Text to PO content', 'From PO content to text']]);
		echo $this->Form->input('quotes', ['type'=>'select', 'options' => ['Do nothing', 'Remove smart quotes', 'Add smart quotes']]);
		echo $this->Form->input('newline', ['type'=>'select', 'options' => ['\n', '<br/>']]);
		echo $this->Form->input('input', ['type'=>'textarea', 'rows' => 20]);
	?>
	</fieldset>

<?php echo $this->Form->submit(__d('translate', 'Submit')); echo $this->Form->end();?>
</div>
