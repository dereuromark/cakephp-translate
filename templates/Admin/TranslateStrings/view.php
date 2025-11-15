<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateString $translateString
 */

use Cake\Core\Configure;

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
					'<i class="fas fa-edit"></i> ' . __d('translate', 'Edit Translate String'),
					['action' => 'edit', $translateString->id],
					['escape' => false, 'class' => 'list-group-item list-group-item-action'],
				) ?>
				<?= $this->Html->link(
					'<i class="fas fa-language"></i> ' . __d('translate', 'Translate'),
					['action' => 'translate', $translateString->id],
					['escape' => false, 'class' => 'list-group-item list-group-item-action'],
				) ?>
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
				<?= $this->Html->link(
					'<i class="fas fa-plus"></i> ' . __d('translate', 'New Translate String'),
					['action' => 'add'],
					['escape' => false, 'class' => 'list-group-item list-group-item-action'],
				) ?>
			</div>
		</div>
	</nav>

	<!-- Main Content -->
	<div class="col-lg-9 col-md-8">
		<!-- String Details Card -->
		<div class="card mb-4">
			<div class="card-header">
				<h2 class="mb-0">
					<i class="fas fa-eye"></i>
					<?= __d('translate', 'Translate String Details') ?>
				</h2>
			</div>
			<div class="card-body">
				<h5 class="text-muted mb-3"><?= __d('translate', 'Original String') ?></h5>
				<div class="alert alert-light border mb-4">
					<pre class="mb-0" style="white-space: pre-wrap;"><?= h($translateString->name) ?></pre>
				</div>

				<div class="row g-3">
					<?php if ($translateString->has('user')) { ?>
					<div class="col-md-6">
						<strong><i class="fas fa-user"></i> <?= __d('translate', 'User') ?></strong><br>
						<span class="text-muted">
							<?= $this->Html->link($translateString->user->email, ['controller' => 'Users', 'action' => 'view', $translateString->user->id]) ?>
						</span>
					</div>
					<?php } ?>

					<div class="col-md-6">
						<strong><i class="fas fa-folder"></i> <?= __d('translate', 'Domain') ?></strong><br>
						<span class="text-muted">
							<?= $this->Html->link($translateString->translate_domain->name, ['controller' => 'TranslateDomains', 'action' => 'view', $translateString->translate_domain->id]) ?>
						</span>
					</div>

					<div class="col-md-6">
						<strong><i class="fas fa-toggle-<?= $translateString->active ? 'on text-success' : 'off text-muted' ?>"></i> <?= __d('translate', 'Active') ?></strong><br>
						<span class="text-muted"><?= $this->element('Translate.yes_no', ['value' => $translateString->active]) ?></span>
					</div>

					<div class="col-md-6">
						<strong><i class="fas fa-code"></i> <?= __d('translate', 'Is HTML') ?></strong><br>
						<span class="text-muted"><?= $this->element('Translate.yes_no', ['value' => $translateString->is_html]) ?></span>
					</div>

					<div class="col-md-6">
						<strong><i class="fas fa-download"></i> <?= __d('translate', 'Last Import') ?></strong><br>
						<span class="text-muted"><?= $translateString->last_import ? $this->Time->nice($translateString->last_import) : '<em>N/A</em>' ?></span>
					</div>

					<div class="col-md-6">
						<strong><i class="fas fa-clock"></i> <?= __d('translate', 'Created') ?></strong><br>
						<span class="text-muted"><?= $this->Time->nice($translateString->created) ?></span>
					</div>

					<div class="col-md-6">
						<strong><i class="fas fa-edit"></i> <?= __d('translate', 'Modified') ?></strong><br>
						<span class="text-muted"><?= $this->Time->nice($translateString->modified) ?></span>
					</div>
				</div>
			</div>
		</div>

		<!-- Translations Card -->
		<div class="card mb-4">
			<div class="card-header">
				<h3 class="mb-0">
					<i class="fas fa-language"></i>
					<?= __d('translate', 'Translations') ?>
				</h3>
			</div>
			<div class="card-body">
				<?php if ($translateString->translate_terms) { ?>
					<div class="table-responsive">
						<table class="table table-striped table-hover">
							<thead>
								<tr>
									<th><i class="fas fa-flag"></i> <?= __d('translate', 'Language') ?></th>
									<th><i class="fas fa-comment"></i> <?= __d('translate', 'Translation') ?></th>
									<th><i class="fas fa-check-circle"></i> <?= __d('translate', 'Status') ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($translateString->translate_terms as $translateTerm) { ?>
									<tr>
										<td>
											<span class="badge bg-secondary">
												<?= h($translateTerm->translate_language->iso2) ?>
											</span>
											<?= h($translateTerm->translate_language->name) ?>
										</td>
										<td>
											<?php if ($translateTerm->content !== '') { ?>
												<span><?= h($translateTerm->content) ?></span>
											<?php } elseif ($translateTerm->translate_language->locale === Configure::read('Translate.defaultLocale')) { ?>
												<span class="text-muted fst-italic" title="<?= __d('translate', 'Using default value') ?>">
													<i class="fas fa-info-circle"></i> <?= h($translateString->name) ?>
												</span>
											<?php } else { ?>
												<span class="text-danger fst-italic">
													<i class="fas fa-exclamation-triangle"></i> <?= __d('translate', 'Not translated') ?>
												</span>
											<?php } ?>
										</td>
										<td>
											<?php if ($translateTerm->confirmed) { ?>
												<span class="badge bg-success">
													<i class="fas fa-check"></i> <?= __d('translate', 'Confirmed') ?>
												</span>
											<?php } else { ?>
												<span class="badge bg-warning text-dark">
													<i class="fas fa-clock"></i> <?= __d('translate', 'Pending') ?>
												</span>
											<?php } ?>
										</td>
									</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				<?php } else { ?>
					<div class="alert alert-warning mb-0">
						<i class="fas fa-exclamation-triangle"></i>
						<?= __d('translate', 'No translations available yet.') ?>
						<?= $this->Html->link(__d('translate', 'Translate now'), ['action' => 'translate', $translateString->id], ['class' => 'alert-link']) ?>
					</div>
				<?php } ?>
			</div>
		</div>

		<!-- References Card -->
		<?php
		$references = [];
		if ($translateString->references) {
			$sep = explode(PHP_EOL, $translateString->references);
			foreach ($sep as $s) {
				$s = trim($s);
				if ($s !== '') {
					$references[] = $s;
				}
			}
		}
		?>
		<?php if ($references) { ?>
		<div class="card mb-4">
			<div class="card-header">
				<h3 class="mb-0">
					<i class="fas fa-code"></i>
					<?= __d('translate', 'Code References') ?>
					<span class="badge bg-primary"><?= count($references) ?></span>
				</h3>
			</div>
			<div class="card-body">
				<ul class="list-group list-group-flush">
					<?php foreach ($references as $key => $reference) { ?>
						<li class="list-group-item">
							<i class="fas fa-file-code text-muted"></i>
							<?php if ($this->Translation->canDisplayReference($translateString->translate_domain)) { ?>
								<?= $this->Html->link(
									h($reference),
									['action' => 'displayReference', $translateString->id, $key],
									[
										'class' => 'text-decoration-none code-reference-link',
										'data-reference-url' => $this->Url->build(['action' => 'displayReference', $translateString->id, $key]),
									],
								) ?>
								<i class="fas fa-search-plus fa-xs text-muted ms-1"></i>
							<?php } else { ?>
								<code><?= h($reference) ?></code>
							<?php } ?>
						</li>
					<?php } ?>
				</ul>
			</div>
		</div>
		<?php } ?>

		<!-- Related Domain Card -->
		<?php if (!empty($translateString->translate_domain)) { ?>
		<div class="card">
			<div class="card-header">
				<h3 class="mb-0">
					<i class="fas fa-folder-open"></i>
					<?= __d('translate', 'Domain Information') ?>
				</h3>
			</div>
			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-sm">
						<tbody>
							<tr>
								<th width="30%"><i class="fas fa-tag"></i> <?= __d('translate', 'Name') ?></th>
								<td><?= h($translateString->translate_domain->name) ?></td>
							</tr>
							<tr>
								<th><i class="fas fa-toggle-on"></i> <?= __d('translate', 'Active') ?></th>
								<td><?= $this->element('Translate.yes_no', ['value' => $translateString->translate_domain->active]) ?></td>
							</tr>
							<tr>
								<th><i class="fas fa-sort-amount-up"></i> <?= __d('translate', 'Priority') ?></th>
								<td><?= h($translateString->translate_domain->prio) ?></td>
							</tr>
							<tr>
								<th><i class="fas fa-folder"></i> <?= __d('translate', 'Path') ?></th>
								<td><code><?= h($translateString->translate_domain->path) ?></code></td>
							</tr>
							<tr>
								<th><i class="fas fa-clock"></i> <?= __d('translate', 'Created') ?></th>
								<td><?= $this->Time->nice($translateString->translate_domain->created) ?></td>
							</tr>
							<tr>
								<th><i class="fas fa-edit"></i> <?= __d('translate', 'Modified') ?></th>
								<td><?= $this->Time->nice($translateString->translate_domain->modified) ?></td>
							</tr>
						</tbody>
					</table>
				</div>
				<div class="mt-3">
					<?= $this->Html->link(
						'<i class="fas fa-eye"></i> ' . __d('translate', 'View Domain'),
						['controller' => 'TranslateDomains', 'action' => 'view', $translateString->translate_domain->id],
						['escape' => false, 'class' => 'btn btn-sm btn-outline-primary'],
					) ?>
					<?= $this->Html->link(
						'<i class="fas fa-edit"></i> ' . __d('translate', 'Edit Domain'),
						['controller' => 'TranslateDomains', 'action' => 'edit', $translateString->translate_domain->id],
						['escape' => false, 'class' => 'btn btn-sm btn-outline-secondary'],
					) ?>
				</div>
			</div>
		</div>
		<?php } ?>
	</div>
</div>

<!-- Modal for code references -->
<div class="modal fade" id="codeReferenceModal" tabindex="-1" aria-labelledby="codeReferenceModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl modal-dialog-scrollable">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="codeReferenceModalLabel">
					<i class="fas fa-file-code"></i> <?= __d('translate', 'Code Reference') ?>
				</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body" id="codeReferenceContent">
				<div class="text-center py-5">
					<div class="spinner-border text-primary" role="status">
						<span class="visually-hidden"><?= __d('translate', 'Loading...') ?></span>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php $this->Html->scriptStart(['block' => true]); ?>
document.addEventListener('DOMContentLoaded', function() {
	const links = document.querySelectorAll('.code-reference-link');
	const modal = new bootstrap.Modal(document.getElementById('codeReferenceModal'));
	const modalBody = document.getElementById('codeReferenceContent');

	links.forEach(link => {
		link.addEventListener('click', function(e) {
			e.preventDefault();
			const url = this.dataset.referenceUrl;

			// Show loading spinner
			modalBody.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';

			// Open modal
			modal.show();

			// Load content via AJAX
			fetch(url, {
				headers: {
					'X-Requested-With': 'XMLHttpRequest'
				}
			})
			.then(response => response.text())
			.then(html => {
				modalBody.innerHTML = html;
			})
			.catch(error => {
				modalBody.innerHTML = '<div class="alert alert-danger">Error loading reference</div>';
				console.error('Error:', error);
			});
		});
	});
});
<?php $this->Html->scriptEnd(); ?>
