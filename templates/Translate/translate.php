<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\Translate\Model\Entity\TranslateLocale> $translateLocales
 * @var \Translate\Model\Entity\TranslateString $translateString
 * @var array $suggestions
 * @var array $domainStats
 */

?>
<!-- Domain Navigation -->
<div class="mb-4">
	<div class="card">
		<div class="card-header d-flex justify-content-between align-items-center">
			<h5 class="mb-0">
				<i class="fas fa-folder"></i>
				<?= __d('translate', 'Domains') ?>
			</h5>
			<?= $this->Html->link(
				'<i class="fas fa-arrow-left"></i> ' . __d('translate', 'Overview'),
				['controller' => 'Translate', 'action' => 'index'],
				['class' => 'btn btn-sm btn-secondary', 'escape' => false]
			) ?>
		</div>
		<div class="card-body p-2">
			<div class="row g-2">
				<?php foreach ($domainStats as $domainId => $stats) { ?>
					<div class="col-md-3 col-sm-6">
						<?php
						$isActive = $stats['name'] === $translateString->translate_domain->name;
						$cardClass = $isActive ? 'border-primary' : 'border-secondary';
						$bgClass = $isActive ? 'bg-primary text-white' : '';
						?>
						<div class="card <?= $cardClass ?> h-100">
							<div class="card-body p-2 <?= $bgClass ?>">
								<h6 class="mb-1">
									<?php if ($isActive) { ?>
										<i class="fas fa-check-circle"></i>
									<?php } ?>
									<?= h($stats['name']) ?>
								</h6>
								<small class="d-block">
									<i class="fas fa-file-alt"></i>
									<?= $stats['translated'] ?> / <?= $stats['total'] ?>
									<?= __d('translate', 'strings') ?>
								</small>
								<div class="progress mt-2" style="height: 5px;">
									<div class="progress-bar bg-success"
										role="progressbar"
										style="width: <?= $stats['percentage'] ?>%"
										aria-valuenow="<?= $stats['percentage'] ?>"
										aria-valuemin="0"
										aria-valuemax="100">
									</div>
								</div>
								<small class="d-block mt-1">
									<strong><?= $stats['percentage'] ?>%</strong>
									<?= __d('translate', 'complete') ?>
								</small>
							</div>
						</div>
					</div>
				<?php } ?>
			</div>
		</div>
	</div>
</div>

<div class="translateStrings index">

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
		foreach ($translateLocales as $translateLocale) {
			$key = $translateLocale->locale;
			$formKey = str_replace('_', '-', strtolower($translateLocale->locale));
			echo $this->Form->control('content_' . strtolower($translateLocale->locale), ['type' => 'text', 'label' => __d('translate', 'Singular') . ' ' . $translateLocale->locale, 'rel' => $formKey]);
			if (!empty($suggestions[$key])) {
				echo $this->element('suggestions', ['suggestions' => $suggestions[$key], 'key' => $formKey]);
			}
		}

		foreach ($translateLocales as $translateLocale) {
			$key = $translateLocale->locale;
			$formKey = str_replace('_', '-', strtolower($translateLocale->locale));
			//TODO add plural 3 to 6 if necessary
			echo $this->Form->control('plural_2_' . strtolower($translateLocale->locale), ['type' => 'text', 'label' => __d('translate', 'Plural') . ' ' . $translateLocale->locale, 'rel' => 'p' . $formKey]);

		}

	} else {

		foreach ($translateLocales as $translateLocale) {
			$key = $translateLocale->locale;
			$formKey = str_replace('_', '-', strtolower($translateLocale->locale));
			echo $this->Form->control('content_' . strtolower($translateLocale->locale), ['type' => 'textarea', 'label' => h($translateLocale->locale), 'rel' => $formKey]);
			if (!empty($suggestions[$key])) {
				echo $this->element('suggestions', ['suggestions' => $suggestions[$key], 'key' => $formKey]);
			}
		}
	}

	?>
	</fieldset>

	<div class="form-group mt-4 mb-4">
		<div class="d-flex gap-2">
			<?php echo $this->Form->submit(__d('translate', 'Save'), ['name' => 'save', 'class' => 'btn btn-primary']); ?>
			<?php echo $this->Form->submit(__d('translate', 'Save') . ' + ' . __d('translate', 'Next'), ['name' => 'next', 'class' => 'btn btn-success']); ?>
			<?php echo $this->Form->submit(__d('translate', 'Skip'), ['name' => 'skip', 'class' => 'btn btn-secondary']); ?>
		</div>
	</div>
<?php echo $this->Form->end(); ?>
<br/>
<?php
$sep = explode(PHP_EOL, $translateString->references);
$references = [];
foreach ($sep as $s) {
	$s = trim($s);
	if ($s !== '') {
		$references[] = $s;
	}
}

?>

<h3>Additional Infos</h3>
Domain: <code><?php echo h($translateString->translate_domain->name); ?></code><br/>

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
