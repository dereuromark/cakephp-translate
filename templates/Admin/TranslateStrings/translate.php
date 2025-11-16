<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\Translate\Model\Entity\TranslateLocale> $translateLocales
 * @var \Translate\Model\Entity\TranslateString $translateString
 * @var array $suggestions
 */

?>
<div class="row">
	<aside class="col-md-3 col-sm-4 col-12">
		<div class="card">
			<div class="card-header">
				<h3 class="card-title"><i class="fa-solid fa-bars"></i> <?= __d('translate', 'Actions') ?></h3>
			</div>
			<div class="list-group list-group-flush">
				<?= $this->Html->link(__d('translate', 'Overview'), ['controller' => 'Translate', 'action' => 'index'], ['class' => 'list-group-item list-group-item-action']) ?>
				<?= $this->Html->link(__d('translate', 'List Translate Strings'), ['controller' => 'TranslateStrings', 'action' => 'index', '?' => $this->request->getQuery()], ['class' => 'list-group-item list-group-item-action']) ?>
				<?= $this->Html->link(__d('translate', 'Edit Translate String'), ['action' => 'edit', $translateString['id']], ['class' => 'list-group-item list-group-item-action']) ?>
			</div>
		</div>
	</aside>

	<div class="col-md-9 col-sm-8 col-12">
		<div class="card">
			<div class="card-header">
				<h3 class="card-title"><i class="fa-solid fa-language"></i> <?= __d('translate', 'Translate String') ?></h3>
				<div class="card-tools">
					<?= $this->Html->link($this->Icon->render('edit'), ['action' => 'edit', $translateString->id, '?' => ['translate_afterwards' => true]], ['escape' => false, 'class' => 'btn btn-tool']); ?>
				</div>
			</div>
			<div class="card-body">
				<div class="mb-3">
					<strong><?= __d('translate', 'String:') ?></strong>
					<code><?= h($translateString['name']) ?></code>
					<?php if ($translateString->plural) { ?>
						/ <code><?= h($translateString->plural) ?></code>
					<?php } ?>
				</div>

				<?php if ($translateString->is_html) { ?>
					<div class="alert alert-warning">
						<i class="fa-solid fa-exclamation-triangle"></i>
						<strong><?= __d('translate', 'HTML') ?></strong> - <?= __d('translate', 'Manual escaping necessary!') ?>
					</div>
				<?php } ?>

				<?php
				// Extract and display placeholders
				$placeholders = [];
				preg_match_all('/\{\d+\}/', $translateString->name, $braceMatches);
				preg_match_all('/%(?:\d+\$)?[sdfboxXeEgGcup]/', $translateString->name, $sprintfMatches);
				$placeholders = array_merge($braceMatches[0], $sprintfMatches[0]);
				if ($placeholders) {
					?>
					<div class="alert alert-info">
						<i class="fa-solid fa-info-circle"></i>
						<strong><?= __d('translate', 'Required placeholders:') ?></strong>
						<?php foreach (array_unique($placeholders) as $placeholder) { ?>
							<code><?= h($placeholder) ?></code>
						<?php } ?>
					</div>
					<?php
				}
				?>

				<?= $this->Form->create($translateString) ?>
					<fieldset>
						<legend><?= __d('translate', 'Translate This String') ?></legend>

					<?php
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

					<div class="btn-group">
						<?= $this->Form->button(__d('translate', 'Save'), ['name' => 'save', 'value' => 'Task', 'class' => 'btn btn-primary']) ?>
						<?= $this->Form->button(__d('translate', 'Save') . ' + ' . __d('translate', 'Next'), ['name' => 'next', 'value' => 'Task', 'class' => 'btn btn-success']) ?>
						<?= $this->Form->button(__d('translate', 'Skip'), ['name' => 'skip', 'value' => 'skip', 'class' => 'btn btn-secondary']) ?>
					</div>

				<?= $this->Form->end() ?>
			</div>
		</div>

		<div class="card mt-3">
			<div class="card-header">
				<h3 class="card-title"><i class="fa-solid fa-info-circle"></i> <?= __d('translate', 'Additional Information') ?></h3>
			</div>
			<div class="card-body">
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

				<div class="mb-3">
					<strong><?= __d('translate', 'Domain:') ?></strong>
					<?= $this->Html->link($translateString->translate_domain->name, ['action' => 'index', '?' => ['translate_domain_id' => $translateString->translate_domain_id]]) ?>
				</div>

				<div>
					<strong><?= __d('translate', 'References:') ?></strong>
					<?= count($references) ?>x
					<?php if ($references) { ?>
						<ul class="references mt-2">
							<?php foreach ($references as $key => $reference) { ?>
								<li>
									<?php if ($this->Translation->canDisplayReference($translateString->translate_domain)) { ?>
										<?= $this->Html->link($reference, ['action' => 'displayReference', $translateString->id, $key], ['class' => 'reference-link', 'target' => '_blank']) ?>
									<?php } else { ?>
										<?= h($reference) ?>
									<?php } ?>
								</li>
							<?php } ?>
						</ul>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Modal -->
<div class="modal fade" id="modelLg" tabindex="-1" aria-labelledby="modelLgLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="modelLgLabel"><?= __d('translate', 'Code Reference') ?></h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= __d('translate', 'Close') ?></button>
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
