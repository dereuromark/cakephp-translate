<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateLocale $translateLocale
 */
?>
<div class="row">
	<aside class="col-md-3">
		<div class="card">
			<div class="card-header">
				<h3 class="card-title"><i class="fa fa-bars"></i> <?= __d('translate', 'Actions') ?></h3>
			</div>
			<div class="list-group list-group-flush">
				<?= $this->Html->link(__d('translate', 'Edit Locale'), ['action' => 'edit', $translateLocale->id], ['class' => 'list-group-item list-group-item-action']) ?>
				<?= $this->Form->postLink(__d('translate', 'Delete Locale'), ['action' => 'delete', $translateLocale->id], ['class' => 'list-group-item list-group-item-action text-danger', 'confirm' => __d('translate', 'Are you sure you want to delete # {0}?', $translateLocale->id)]) ?>
				<?= $this->Html->link(__d('translate', 'List Locales'), ['action' => 'index'], ['class' => 'list-group-item list-group-item-action']) ?>
				<?= $this->Html->link(__d('translate', 'New Locale'), ['action' => 'add'], ['class' => 'list-group-item list-group-item-action']) ?>
			</div>
		</div>
	</aside>
	<div class="col-md-9">
		<div class="card">
			<div class="card-header">
				<h3 class="card-title"><i class="fa fa-flag"></i> <?= h($translateLocale->name) ?></h3>
			</div>
			<div class="card-body p-0">
				<table class="table table-striped mb-0">
					<tr>
						<th><?= __d('translate', 'Language Relation') ?></th>
						<td><?= $translateLocale->language_id && !empty($translateLocale->language) ? h($translateLocale->language->name) : ($translateLocale->language_id ?: '-') ?></td>
					</tr>
					<tr>
						<th><?= __d('translate', 'Name') ?></th>
						<td><?= h($translateLocale->name) ?></td>
					</tr>
					<tr>
						<th><?= __d('translate', 'Locale') ?></th>
						<td><?= h($translateLocale->locale) ?></td>
					</tr>
					<tr>
						<th><?= __d('translate', 'Iso2') ?></th>
						<td><?= h($translateLocale->iso2) ?></td>
					</tr>
					<tr>
						<th><?= __d('translate', 'Active') ?></th>
						<td><?= $this->element('Translate.yes_no', ['value' => $translateLocale->active]) ?></td>
					</tr>
				</table>
			</div>
		</div>
	</div>
</div>
