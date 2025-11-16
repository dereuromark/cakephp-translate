<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateTerm $translateTerm
 */

use Cake\Core\Plugin;

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
					'<i class="fas fa-edit"></i> ' . __d('translate', 'Edit Translate Term'),
					['action' => 'edit', $translateTerm->id],
					['escape' => false, 'class' => 'list-group-item list-group-item-action'],
				) ?>
				<?= $this->Form->postLink(
					'<i class="fas fa-trash"></i> ' . __d('translate', 'Delete Translate Term'),
					['action' => 'delete', $translateTerm->id],
					['confirm' => __d('translate', 'Are you sure you want to delete # {0}?', $translateTerm->id), 'escape' => false, 'class' => 'list-group-item list-group-item-action text-danger', 'block' => true],
				) ?>
				<?= $this->Html->link(
					'<i class="fas fa-comments"></i> ' . __d('translate', 'List Translate Terms'),
					['action' => 'index'],
					['escape' => false, 'class' => 'list-group-item list-group-item-action'],
				) ?>
				<?= $this->Html->link(
					'<i class="fas fa-language"></i> ' . __d('translate', 'List Translate Strings'),
					['controller' => 'TranslateStrings', 'action' => 'index'],
					['escape' => false, 'class' => 'list-group-item list-group-item-action'],
				) ?>
				<?= $this->Html->link(
					'<i class="fas fa-plus-circle"></i> ' . __d('translate', 'New Translate String'),
					['controller' => 'TranslateStrings', 'action' => 'add'],
					['escape' => false, 'class' => 'list-group-item list-group-item-action'],
				) ?>
			</div>
		</div>
	</nav>

	<!-- Main Content -->
	<div class="col-lg-9 col-md-8">
		<div class="page-header mb-4">
			<h1><i class="fas fa-comments"></i> <?= h($translateTerm->content) ?></h1>
		</div>

		<!-- Details Card -->
		<div class="card">
			<div class="card-header">
				<i class="fas fa-info-circle"></i> <?= __d('translate', 'Translate Term Details') ?>
			</div>
			<div class="card-body">
				<table class="table table-striped">
					<tbody>
						<tr>
							<th><?= __d('translate', 'Translate String') ?></th>
							<td>
								<?php if ($translateTerm->has('translate_string')) { ?>
									<?= $this->Html->link(
										$translateTerm->translate_string->name,
										['controller' => 'TranslateStrings', 'action' => 'view', $translateTerm->translate_string->id],
									) ?>
								<?php } ?>
							</td>
						</tr>
						<tr>
							<th><?= __d('translate', 'Comment') ?></th>
							<td><?= h($translateTerm->comment) ?></td>
						</tr>
						<tr>
							<th><?= __d('translate', 'Locale') ?></th>
							<td>
								<?php if ($translateTerm->has('translate_locale')) { ?>
									<?= $this->Html->link(
										$translateTerm->translate_locale->name,
										['controller' => 'TranslateLocales', 'action' => 'view', $translateTerm->translate_locale->id],
									) ?>
								<?php } ?>
							</td>
						</tr>
						<tr>
							<th><?= __d('translate', 'User Id') ?></th>
							<td><?= h($translateTerm->user_id) ?></td>
						</tr>
						<tr>
							<th><?= __d('translate', 'Confirmed') ?></th>
							<td><?= $this->element('Translate.yes_no', ['value' => $translateTerm->confirmed]) ?></td>
						</tr>
						<tr>
							<th><?= __d('translate', 'Confirmed By') ?></th>
							<td><?= h($translateTerm->confirmed_by) ?></td>
						</tr>
						<tr>
							<th><?= __d('translate', 'Created') ?></th>
							<td><?= $this->Time->nice($translateTerm->created) ?></td>
						</tr>
						<tr>
							<th><?= __d('translate', 'Modified') ?></th>
							<td><?= $this->Time->nice($translateTerm->modified) ?></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>

		<!-- Content Card -->
		<div class="card mt-4">
			<div class="card-header">
				<i class="fas fa-file-alt"></i> <?= __d('translate', 'Content') ?>
			</div>
			<div class="card-body">
				<?= $this->Text->autoParagraph(h($translateTerm->content)) ?>
			</div>
		</div>
	</div>
</div>
