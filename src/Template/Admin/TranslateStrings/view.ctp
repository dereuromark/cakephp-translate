<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateString $translateString
 */
?>
<nav class="actions col-sm-4 col-xs-12">
	<ul class="side-nav nav nav-pills nav-stacked">
		<li class="heading"><?= __d('translate', 'Actions') ?></li>
		<li><?= $this->Html->link(__d('translate', 'Edit Translate String'), ['action' => 'edit', $translateString->id]) ?> </li>
		<li><?= $this->Form->postLink(__d('translate', 'Delete Translate String'), ['action' => 'delete', $translateString->id], ['confirm' => __d('translate', 'Are you sure you want to delete # {0}?', $translateString->id)]) ?> </li>
		<li><?= $this->Html->link(__d('translate', 'List Translate Strings'), ['action' => 'index']) ?> </li>
		<li><?= $this->Html->link(__d('translate', 'New Translate String'), ['action' => 'add']) ?> </li>
	</ul>
</nav>
<div class="translateStrings view col-sm-8 col-xs-12">
	<h2><?= h($translateString->name) ?></h2>
	<table class="table vertical-table">
		<tr>
			<th><?= __d('translate', 'User') ?></th>
			<td><?= $translateString->has('user') ? $this->Html->link($translateString->user->email, ['controller' => 'Users', 'action' => 'view', $translateString->user->id]) : '' ?></td>
		</tr>
		<tr>
			<th><?= __d('translate', 'Active') ?></th>
			<td><?= $this->Format->yesNo($translateString->active) ?></td>
		</tr>
		<tr>
			<th><?= __d('translate', 'Is Html') ?></th>
			<td><?= $this->Format->yesNo($translateString->is_html) ?></td>
		</tr>
		<tr>
			<th><?= __d('translate', 'Last Import') ?></th>
			<td><?= $this->Time->nice($translateString->last_import) ?></td>
		</tr>
		<tr>
			<th><?= __d('translate', 'Created') ?></th>
			<td><?= $this->Time->nice($translateString->created) ?></td>
		</tr>
		<tr>
			<th><?= __d('translate', 'Modified') ?></th>
			<td><?= $this->Time->nice($translateString->modified) ?></td>
		</tr>
	</table>
	<div class="row">
		<h3><?= __d('translate', 'Name') ?></h3>
		<?= $this->Text->autoParagraph(h($translateString->name)); ?>
	</div>
	<div class="row">
		<h3><?= __d('translate', 'Description') ?></h3>
		<?= $this->Text->autoParagraph(h($translateString->description)); ?>
	</div>
	<div class="row">
		<h3><?= __d('translate', 'Occurrences') ?></h3>
		<?= $this->Text->autoParagraph(h($translateString->occurrences)); ?>
	</div>

	<div class="related">
		<h3><?= __d('translate', 'Related Translate Groups') ?></h3>
		<?php if (!empty($translateString->translate_groups)): ?>
		<table class="table table-horizontal">
									<tr>
			<th><?= __d('translate', 'Name') ?></th>
						<tr>
			<th><?= __d('translate', 'Project Id') ?></th>
						<tr>
			<th><?= __d('translate', 'Active') ?></th>
						<tr>
			<th><?= __d('translate', 'Prio') ?></th>
						<tr>
			<th><?= __d('translate', 'Created') ?></th>
						<tr>
			<th><?= __d('translate', 'Modified') ?></th>
				<th class="actions"><?= __d('translate', 'Actions') ?></th>
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
					<?= $this->Html->link(__d('translate', 'View'), ['controller' => 'TranslateGroups', 'action' => 'view', $translateGroups->id]) ?>

					<?= $this->Html->link(__d('translate', 'Edit'), ['controller' => 'TranslateGroups', 'action' => 'edit', $translateGroups->id]) ?>

					<?= $this->Form->postLink(__d('translate', 'Delete'), ['controller' => 'TranslateGroups', 'action' => 'delete', $translateGroups->id], ['confirm' => __d('translate', 'Are you sure you want to delete # {0}?', $translateGroups->id)]) ?>

				</td>
			</tr>
			<?php endforeach; ?>
		</table>
	<?php endif; ?>
	</div>
</div>
