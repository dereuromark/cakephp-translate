<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateProject[] $translateProjects
 */
?>
<nav class="actions col-sm-4 col-xs-12">
	<ul class="side-nav nav nav-pills nav-stacked">
		<li class="heading"><?= __('Actions') ?></li>
		<li><?= $this->Html->link(__('Overview'), ['controller' => 'Translate', 'action' => 'index']) ?></li>
		<li><?= $this->Html->link(__('New Translate Project'), ['action' => 'add']) ?></li>
		<li><?= $this->Html->link(__('List Translate Languages'), ['controller' => 'TranslateLanguages', 'action' => 'index']) ?> </li>
		<li><?= $this->Html->link(__('List Translate Groups'), ['controller' => 'TranslateGroups', 'action' => 'index']) ?> </li>
		<li><?= $this->Html->link(__('List Translate Strings'), ['controller' => 'TranslateStrings', 'action' => 'index']) ?></li>
	</ul>
</nav>
<div class="translateProjects index col-sm-8 col-xs-12">
	<h3><?= __('Translate Projects') ?></h3>
	<table class="table table-striped">
		<thead>
			<tr>
				<th><?= $this->Paginator->sort('name') ?></th>
				<th><?= $this->Paginator->sort('type') ?></th>
				<th><?= $this->Paginator->sort('default') ?></th>
				<th><?= $this->Paginator->sort('status') ?></th>
				<th><?= $this->Paginator->sort('created', null, ['direction' => 'desc']) ?></th>
				<th><?= $this->Paginator->sort('modified', null, ['direction' => 'desc']) ?></th>
				<th class="actions"><?= __('Actions') ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($translateProjects as $translateProject): ?>
			<tr>
				<td><?= h($translateProject->name) ?></td>
				<td><?= $this->Number->format($translateProject->type) ?></td>
				<td><?= $this->Format->yesNo($translateProject->default) ?></td>
				<td><?= $this->Number->format($translateProject->status) ?></td>
				<td><?= $this->Time->nice($translateProject->created) ?></td>
				<td><?= $this->Time->nice($translateProject->modified) ?></td>
				<td class="actions">
				<?= $this->Html->link($this->Format->icon('view'), ['action' => 'view', $translateProject->id], ['escape' => false]); ?>
				<?= $this->Html->link($this->Format->icon('edit'), ['action' => 'edit', $translateProject->id], ['escape' => false]); ?>
				<?= $this->Form->postLink($this->Format->icon('delete'), ['action' => 'delete', $translateProject->id], ['escape' => false, 'confirm' => __('Are you sure you want to delete # {0}?', $translateProject->id)]); ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<?php echo $this->element('Tools.pagination'); ?>
</div>
