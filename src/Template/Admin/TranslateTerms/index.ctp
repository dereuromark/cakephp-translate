<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateTerm[] $translateTerms
 */
?>
<nav class="actions col-sm-4 col-xs-12">
	<ul class="side-nav nav nav-pills nav-stacked">
		<li class="heading"><?= __('Actions') ?></li>
		<li><?= $this->Html->link(__('New Translate Term'), ['action' => 'add']) ?></li>
		<li><?= $this->Html->link(__('List Translate Strings'), ['controller' => 'TranslateStrings', 'action' => 'index']) ?></li>
		<li><?= $this->Html->link(__('New Translate String'), ['controller' => 'TranslateStrings', 'action' => 'add']) ?></li>
		<li><?= $this->Html->link(__('List Translate Languages'), ['controller' => 'TranslateLanguages', 'action' => 'index']) ?></li>
		<li><?= $this->Html->link(__('New Translate Language'), ['controller' => 'TranslateLanguages', 'action' => 'add']) ?></li>
	</ul>
</nav>
<div class="translateTerms index col-sm-8 col-xs-12">
	<h3><?= __('Translate Terms') ?></h3>
	<table class="table table-striped">
		<thead>
			<tr>
				<th><?= $this->Paginator->sort('translate_string_id') ?></th>
				<th><?= $this->Paginator->sort('comment') ?></th>
				<th><?= $this->Paginator->sort('translate_language_id') ?></th>
				<th><?= $this->Paginator->sort('user_id') ?></th>
				<th><?= $this->Paginator->sort('confirmed') ?></th>
				<th><?= $this->Paginator->sort('confirmed_by') ?></th>
				<th><?= $this->Paginator->sort('created', null, ['direction' => 'desc']) ?></th>
				<th><?= $this->Paginator->sort('modified', null, ['direction' => 'desc']) ?></th>
				<th class="actions"><?= __('Actions') ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($translateTerms as $translateTerm): ?>
			<tr>
				<td><?= $translateTerm->has('translate_string') ? $this->Html->link($translateTerm->translate_string->name, ['controller' => 'TranslateStrings', 'action' => 'view', $translateTerm->translate_string->id]) : '' ?></td>
				<td><?= h($translateTerm->comment) ?></td>
				<td><?= $translateTerm->has('translate_language') ? $this->Html->link($translateTerm->translate_language->name, ['controller' => 'TranslateLanguages', 'action' => 'view', $translateTerm->translate_language->id]) : '' ?></td>
				<td><?= h($translateTerm->user_id) ?></td>
				<td><?= $this->Format->yesNo($translateTerm->confirmed) ?></td>
				<td><?= h($translateTerm->confirmed_by) ?></td>
				<td><?= $this->Time->nice($translateTerm->created) ?></td>
				<td><?= $this->Time->nice($translateTerm->modified) ?></td>
				<td class="actions">
				<?= $this->Html->link($this->Format->icon('view'), ['action' => 'view', $translateTerm->id], ['escape' => false]); ?>
				<?= $this->Html->link($this->Format->icon('edit'), ['action' => 'edit', $translateTerm->id], ['escape' => false]); ?>
				<?= $this->Form->postLink($this->Format->icon('delete'), ['action' => 'delete', $translateTerm->id], ['escape' => false, 'confirm' => __('Are you sure you want to delete # {0}?', $translateTerm->id)]); ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<?php echo $this->element('Tools.pagination'); ?>
</div>
