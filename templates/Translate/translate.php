<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\Translate\Model\Entity\TranslateLanguage> $translateLanguages
 * @var \Translate\Model\Entity\TranslateString $translateString
 * @var array $suggestions
 */

?>
<nav class="actions col-md-3 col-sm-4 col-xs-12">
	<ul class="side-nav nav nav-pills nav-stacked">
		<li class="heading"><?= __d('translate', 'Actions') ?></li>
		<li><?= $this->Html->link(__d('translate', 'Overview'), ['controller' => 'Translate', 'action' => 'index']) ?></li>
	</ul>
</nav>
<div class="translateStrings index col-md-9 col-sm-8 col-xs-12">

<h3>String</h3>

<code>
	 <?php echo h($translateString->name)?>
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
			$key = $translateLanguage->locale;
			$formKey = str_replace('_', '-', strtolower($translateLanguage->locale));
			echo $this->Form->control('content_' . strtolower($translateLanguage->locale), ['type' => 'text', 'label' => __d('translate', 'Singular') . ' ' . $translateLanguage->locale, 'rel' => $formKey]);
			if (!empty($suggestions[$key])) {
				echo $this->element('suggestions', ['suggestions' => $suggestions[$key], 'key' => $formKey]);
			}
		}

		foreach ($translateLanguages as $translateLanguage) {
			$key = $translateLanguage->locale;
			$formKey = str_replace('_', '-', strtolower($translateLanguage->locale));
			//TODO add plural 3 to 6 if necessary
			echo $this->Form->control('plural_2_' . strtolower($translateLanguage->locale), ['type' => 'text', 'label' => __d('translate', 'Plural') . ' ' . $translateLanguage->locale, 'rel' => 'p' . $formKey]);

		}

	} else {

		foreach ($translateLanguages as $translateLanguage) {
			$key = $translateLanguage->locale;
			$formKey = str_replace('_', '-', strtolower($translateLanguage->locale));
			echo $this->Form->control('content_' . strtolower($translateLanguage->locale), ['type' => 'textarea', 'label' => h($translateLanguage->locale), 'rel' => $formKey]);
			if (!empty($suggestions[$key])) {
				echo $this->element('suggestions', ['suggestions' => $suggestions[$key], 'key' => $formKey]);
			}
		}
	}

	?>
	</fieldset>

	<div class="form-group buttons">
		<div class="col-md-offset-4 col-lg-offset-3 col-md-8 col-lg-9">
<?php echo $this->Form->button(__d('translate', 'Save'), ['name' => 'save', 'value' => 'Task', 'class' => 'btn btn-primary']);?>

<?php echo $this->Form->button(__d('translate', 'Save') . ' + ' . __d('translate', 'Next'), ['name' => 'next', 'value' => 'Task', 'class' => 'btn btn-success']);?>

<?php echo $this->Form->button(__d('translate', 'Skip'), ['name' => 'skip', 'value' => 'skip', 'class' => 'btn btn-secondary']);?>

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
Group: <code><?php echo h($translateString->translate_domain->name); ?></code><br/>

References: <?php echo count($references)?>x
	<?php if ($references) { ?>
	<ul class="references">
		<?php foreach ($references as $key => $reference) { ?>
			<?php if ($this->Translation->canDisplayReference($translateString->translate_domain)) { ?>
			<li><?php echo $this->Html->link($reference, ['action' => 'displayReference', $translateString->id, $key], ['class' => 'reference-link', 'target' => '_blank']); ?></li>
		    <?php } else { ?>
			<li><?php echo h($reference); ?></li>
		    <?php } ?>
	    <?php } ?>
	</ul>
	<?php } ?>

</div>



<!-- Modal -->
<div class="modal fade" id="modelLg" tabindex="-1" aria-labelledby="modelLgLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="modelLgLabel">Code reference</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<?php $this->append('script'); ?>
	<script>
		$(function() {
			$('ul.references').on('click', 'a.reference-link', function (e) {
				e.preventDefault();

				const url = $(this).attr('href');
				const modal = new bootstrap.Modal(document.getElementById('modelLg'));

				$('#modelLg .modal-body').html('<div class="text-center p-4">Loading...</div>');

				$.get(url, function (data) {
					$('#modelLg .modal-body').html(data);
				}).fail(function () {
					$('#modelLg .modal-body').html('<div class="text-danger p-4">Failed to load content.</div>');
				});

				modal.show();
			});
		});
	</script>
<?php $this->end();
