<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateString $translateString
 */
?>
<nav class="actions col-sm-4 col-xs-12">
	<ul class="side-nav nav nav-pills nav-stacked">
		<li class="heading"><?= __('Actions') ?></li>
		<li><?= $this->Html->link(__('Edit Translate String'), ['action' => 'edit', $translateString->id]) ?> </li>
		<li><?= $this->Form->postLink(__('Delete Translate String'), ['action' => 'delete', $translateString->id], ['confirm' => __('Are you sure you want to delete # {0}?', $translateString->id)]) ?> </li>
		<li><?= $this->Html->link(__('List Translate Strings'), ['action' => 'index']) ?> </li>
		<li><?= $this->Html->link(__('New Translate String'), ['action' => 'add']) ?> </li>
	</ul>
</nav>
<div class="translateStrings view col-sm-8 col-xs-12">
	<h2><?= h($translateString->name) ?></h2>
	<table class="table vertical-table">
		<tr>
			<th><?= __('User') ?></th>
			<td><?= $translateString->has('user') ? $this->Html->link($translateString->user->email, ['controller' => 'Users', 'action' => 'view', $translateString->user->id]) : '' ?></td>
		</tr>
		<tr>
			<th><?= __('Active') ?></th>
			<td><?= $this->Format->yesNo($translateString->active) ?></td>
		</tr>
		<tr>
			<th><?= __('Is Html') ?></th>
			<td><?= $this->Format->yesNo($translateString->is_html) ?></td>
		</tr>
		<tr>
			<th><?= __('Last Import') ?></th>
			<td><?= $this->Time->nice($translateString->last_import) ?></td>
		</tr>
		<tr>
			<th><?= __('Created') ?></th>
			<td><?= $this->Time->nice($translateString->created) ?></td>
		</tr>
		<tr>
			<th><?= __('Modified') ?></th>
			<td><?= $this->Time->nice($translateString->modified) ?></td>
		</tr>
	</table>
	<div class="row">
		<h3><?= __('Name') ?></h3>
		<?= $this->Text->autoParagraph(h($translateString->name)); ?>
	</div>
	<div class="row">
		<h3><?= __('Description') ?></h3>
		<?= $this->Text->autoParagraph(h($translateString->description)); ?>
	</div>
	<div class="row">
		<h3><?= __('Occurrences') ?></h3>
		<?= $this->Text->autoParagraph(h($translateString->occurrences)); ?>
	</div>

	<div class="related">
		<h3><?= __('Related Translate Groups') ?></h3>
		<?php if (!empty($translateString->translate_groups)): ?>
		<table class="table table-horizontal">
									<tr>
			<th><?= __('Name') ?></th>
						<tr>
			<th><?= __('Project Id') ?></th>
						<tr>
			<th><?= __('Active') ?></th>
						<tr>
			<th><?= __('Prio') ?></th>
						<tr>
			<th><?= __('Created') ?></th>
						<tr>
			<th><?= __('Modified') ?></th>
				<th class="actions"><?= __('Actions') ?></th>
			</tr>
			<?php foreach ($translateString->translate_groups as $translateGroups): ?>
			<tr>
				<td><?= h($translateGroups->id) ?></td>
				<td><?= h($translateGroups->name) ?></td>
				<td><?= h($translateGroups->project_id) ?></td>
				<td><?= h($translateGroups->active) ?></td>
				<td><?= h($translateGroups->prio) ?></td>
				<td><?= h($translateGroups->created) ?></td>
				<td><?= h($translateGroups->modified) ?></td>
				<td class="actions">
					<?= $this->Html->link(__('View'), ['controller' => 'TranslateGroups', 'action' => 'view', $translateGroups->id]) ?>

					<?= $this->Html->link(__('Edit'), ['controller' => 'TranslateGroups', 'action' => 'edit', $translateGroups->id]) ?>

					<?= $this->Form->postLink(__('Delete'), ['controller' => 'TranslateGroups', 'action' => 'delete', $translateGroups->id], ['confirm' => __('Are you sure you want to delete # {0}?', $translateGroups->id)]) ?>

				</td>
			</tr>
			<?php endforeach; ?>
		</table>
	<?php endif; ?>
	</div>
</div>
