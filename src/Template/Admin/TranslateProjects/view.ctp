<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateProject $translateProject
 */
?>
<nav class="actions col-sm-4 col-xs-12">
	<ul class="side-nav nav nav-pills nav-stacked">
		<li class="heading"><?= __('Actions') ?></li>
		<li><?= $this->Html->link(__('Edit Translate Project'), ['action' => 'edit', $translateProject->id]) ?> </li>
		<li><?= $this->Form->postLink(__('Delete Translate Project'), ['action' => 'delete', $translateProject->id], ['confirm' => __('Are you sure you want to delete # {0}?', $translateProject->id)]) ?> </li>
		<li><?= $this->Html->link(__('List Translate Projects'), ['action' => 'index']) ?> </li>
		<li><?= $this->Html->link(__('New Translate Project'), ['action' => 'add']) ?> </li>
		<li><?= $this->Html->link(__('List Translate Groups'), ['controller' => 'TranslateGroups', 'action' => 'index']) ?> </li>
		<li><?= $this->Html->link(__('New Translate Group'), ['controller' => 'TranslateGroups', 'action' => 'add']) ?> </li>
	</ul>
</nav>
<div class="translateProjects view col-sm-8 col-xs-12">
	<h2><?= h($translateProject->name) ?></h2>
	<table class="table vertical-table">
		<tr>
			<th><?= __('Name') ?></th>
			<td><?= h($translateProject->name) ?></td>
		</tr>
		<tr>
			<th><?= __('Type') ?></th>
			<td><?= $this->Number->format($translateProject->type) ?></td>
		</tr>
		<tr>
			<th><?= __('Default') ?></th>
			<td><?= $this->Format->yesNo($translateProject->default) ?></td>
		</tr>
		<tr>
			<th><?= __('Status') ?></th>
			<td><?= $this->Number->format($translateProject->status) ?></td>
		</tr>
		<tr>
			<th><?= __('Created') ?></th>
			<td><?= $this->Time->nice($translateProject->created) ?></td>
		</tr>
		<tr>
			<th><?= __('Modified') ?></th>
			<td><?= $this->Time->nice($translateProject->modified) ?></td>
		</tr>
	</table>

	<div class="related">
		<h3><?= __('Related Translate Groups') ?></h3>
		<?php if (!empty($translateProject->translate_groups)): ?>
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
			<?php foreach ($translateProject->translate_groups as $translateGroups): ?>
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
