<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateString $translateString
 */
?>
<div class="row">
	<!-- Sidebar -->
	<nav class="col-lg-3 col-md-4 mb-4">
		<div class="card mb-3">
			<div class="card-header">
				<i class="fas fa-bars"></i> <?= __d('translate', 'Actions') ?>
			</div>
			<div class="list-group list-group-flush">
				<?= $this->Form->postLink(
					'<i class="fas fa-trash"></i> ' . __d('translate', 'Delete'),
					['action' => 'delete', $translateString->id],
					['confirm' => __d('translate', 'Are you sure you want to delete # {0}?', $translateString->id), 'escape' => false, 'class' => 'list-group-item list-group-item-action text-danger'],
				) ?>
				<?= $this->Html->link(
					'<i class="fas fa-list"></i> ' . __d('translate', 'List Translate Strings'),
					['action' => 'index'],
					['escape' => false, 'class' => 'list-group-item list-group-item-action'],
				) ?>
			</div>
		</div>

		<div class="card">
			<div class="card-header">
				<i class="fas fa-info-circle"></i> <?= __d('translate', 'Field Information') ?>
			</div>
			<div class="card-body">
				<h6 class="fw-bold"><i class="fas fa-code"></i> <?= __d('translate', 'Is HTML') ?></h6>
				<p class="small text-muted mb-3">
					<?= __d('translate', 'Check this if the string contains HTML tags. When enabled, translations will not be automatically escaped, allowing HTML markup.') ?>
					<br><strong><?= __d('translate', 'Use with caution!') ?></strong>
				</p>

				<h6 class="fw-bold"><i class="fas fa-hashtag"></i> <?= __d('translate', 'Placeholders') ?></h6>
				<p class="small text-muted mb-3">
					<?= __d('translate', 'Use {0}, {1}, {2} etc. for dynamic values. Example: "You have {0} items" becomes "You have 5 items"') ?>
				</p>

				<h6 class="fw-bold"><i class="fas fa-info-circle"></i> <?= __d('translate', 'Context Usage') ?></h6>
				<p class="small text-muted mb-0">
					<?= __d('translate', 'Context helps differentiate identical strings with different meanings:') ?>
				</p>
				<ul class="small text-muted mt-1 mb-0">
					<li>"Post" (<?= __d('translate', 'verb') ?>) vs "Post" (<?= __d('translate', 'noun') ?>)</li>
					<li>"Save" (<?= __d('translate', 'button') ?>) vs "Save" (<?= __d('translate', 'menu item') ?>)</li>
				</ul>
			</div>
		</div>
	</nav>

	<!-- Main Content -->
	<div class="col-lg-9 col-md-8">
		<div class="card">
			<div class="card-header">
				<h2 class="mb-0">
					<i class="fas fa-edit"></i>
					<?= __d('translate', 'Edit Translate String') ?>
				</h2>
			</div>
			<div class="card-body">
				<?= $this->Form->create($translateString) ?>
				<fieldset>
					<div class="row g-3">
						<div class="col-md-12">
							<?= $this->Form->control('translate_domain_id', [
								'label' => '<i class="fas fa-folder"></i> ' . __d('translate', 'Domain'),
								'escape' => false,
							]) ?>
							<small class="form-text text-muted">
								<?= __d('translate', 'The translation domain/group this string belongs to (e.g., "default", "validation")') ?>
							</small>
						</div>

						<div class="col-md-12">
							<?= $this->Form->control('name', [
								'label' => '<i class="fas fa-tag"></i> ' . __d('translate', 'Name (Singular)'),
								'escape' => false,
								'rows' => 3,
							]) ?>
							<small class="form-text text-muted">
								<?= __d('translate', 'The source string to be translated. Use {0}, {1} for placeholders.') ?>
							</small>
						</div>

						<div class="col-md-12">
							<?= $this->Form->control('plural', [
								'label' => '<i class="fas fa-list-ol"></i> ' . __d('translate', 'Plural'),
								'escape' => false,
								'rows' => 2,
							]) ?>
							<small class="form-text text-muted">
								<?= __d('translate', 'Optional plural form (e.g., "One item" → "Multiple items"). Must use same placeholders as singular.') ?>
							</small>
						</div>

						<div class="col-md-12">
							<?= $this->Form->control('context', [
								'label' => '<i class="fas fa-info-circle"></i> ' . __d('translate', 'Context'),
								'escape' => false,
								'placeholder' => __d('translate', 'e.g., "navigation", "button", "error message"'),
							]) ?>
							<small class="form-text text-muted">
								<?= __d('translate', 'Optional context to differentiate identical strings with different meanings (e.g., "Post" as noun vs verb).') ?>
							</small>
						</div>

						<div class="col-md-4">
							<div class="form-check form-switch">
								<?= $this->Form->control('is_html', ['type' => 'checkbox', 'label' => '<i class="fas fa-code"></i> ' . __d('translate', 'Is HTML'), 'escape' => false, 'class' => 'form-check-input']) ?>
							</div>
						</div>

						<div class="col-md-4">
							<div class="form-check form-switch">
								<?= $this->Form->control('update_references', ['type' => 'checkbox', 'label' => '<i class="fas fa-sync"></i> ' . __d('translate', 'Update Code References'), 'escape' => false, 'class' => 'form-check-input']) ?>
							</div>
							<small class="form-text text-muted">
								<?= __d('translate', 'Update all occurrences in source files to match the new string') ?>
							</small>
						</div>

						<div class="col-md-4">
							<div class="form-check form-switch">
								<?= $this->Form->control('translate_afterwards', ['type' => 'checkbox', 'label' => '<i class="fas fa-forward"></i> ' . __d('translate', 'Translate Afterwards'), 'escape' => false, 'class' => 'form-check-input']) ?>
							</div>
						</div>
					</div>
				</fieldset>

				<div class="mt-4 d-flex gap-2">
					<?= $this->Form->button(
						'<i class="fas fa-save"></i> ' . __d('translate', 'Submit'),
						['class' => 'btn btn-primary', 'escapeTitle' => false],
					) ?>
					<?= $this->Html->link(
						'<i class="fas fa-times"></i> ' . __d('translate', 'Cancel'),
						['action' => 'index'],
						['class' => 'btn btn-outline-secondary', 'escapeTitle' => false],
					) ?>
				</div>
				<?= $this->Form->end() ?>
			</div>
		</div>
	</div>
</div>
