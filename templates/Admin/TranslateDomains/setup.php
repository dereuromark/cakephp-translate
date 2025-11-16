<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateDomain $translateDomain
 * @var mixed $existingDomains
 * @var mixed $domains
 */
?>
<div class="row">
	<!-- Sidebar -->
	<nav class="col-lg-3 col-md-4 mb-4">
		<div class="card">
			<div class="card-header">
				<i class="fas fa-bars"></i> <?= __d('translate', 'Actions') ?>
			</div>
			<div class="list-group list-group-flush">
				<?= $this->Html->link(
					'<i class="fas fa-list"></i> ' . __d('translate', 'List Translate Domains'),
					['action' => 'index'],
					['escape' => false, 'class' => 'list-group-item list-group-item-action'],
				) ?>
			</div>
		</div>

		<div class="card mt-3">
			<div class="card-header">
				<i class="fas fa-info-circle"></i> <?= __d('translate', 'Information') ?>
			</div>
			<div class="card-body">
				<p class="small text-muted mb-0">
					<?= __d('translate', 'This wizard helps you set up default translation domains for your application.') ?>
				</p>
				<p class="small text-muted mb-0 mt-2">
					<?= __d('translate', 'Select the domains you want to create and submit the form.') ?>
				</p>
			</div>
		</div>
	</nav>

	<!-- Main Content -->
	<div class="col-lg-9 col-md-8">
		<div class="card">
			<div class="card-header">
				<h2 class="mb-0">
					<i class="fas fa-cog"></i>
					<?= __d('translate', 'Setup Default Translate Domains') ?>
				</h2>
			</div>
			<div class="card-body">
				<?= $this->Form->create($translateDomain) ?>
				<fieldset>
					<?php
					$count = 0;
					foreach ($domains as $key => $domainGroup) {
						echo '<h4 class="mt-3 mb-3"><i class="fas fa-folder"></i> ' . h($key) . '</h4>';
						echo '<div class="list-group">';
						foreach ($domainGroup as $domainName) {
							if (in_array($domainName, $existingDomains, true)) {
								echo '<div class="list-group-item">';
								echo '<span class="text-muted">' . h($domainName) . '</span> ';
								echo '<span class="badge bg-secondary">' . __d('translate', 'already exists') . '</span>';
								echo '</div>';
							} else {
								echo '<div class="list-group-item">';
								echo $this->Form->control('TranslateDomain.' . $count . '.confirm', [
									'type' => 'checkbox',
									'label' => h($domainName),
									'class' => 'form-check-input',
								]);
								echo $this->Form->control('TranslateDomain.' . $count . '.name', [
									'type' => 'hidden',
									'value' => $domainName,
								]);
								echo '</div>';
							}
							$count++;
						}
						echo '</div>';
					}
					?>
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
