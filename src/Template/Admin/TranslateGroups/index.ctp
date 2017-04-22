<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateGroup[] $translateGroups
 */
?>
<nav class="actions col-sm-4 col-xs-12">
	<ul class="side-nav nav nav-pills nav-stacked">
		<li class="heading"><?= __('Actions') ?></li>
		<li><?= $this->Html->link(__('New Translate Group'), ['action' => 'add']) ?></li>
		<li><?= $this->Html->link(__('List Translate Projects'), ['controller' => 'TranslateProjects', 'action' => 'index']) ?></li>
		<li><?= $this->Html->link(__('New Translate Project'), ['controller' => 'TranslateProjects', 'action' => 'add']) ?></li>
		<li><?= $this->Html->link(__('List Translate Strings'), ['controller' => 'TranslateStrings', 'action' => 'index']) ?></li>
		<li><?= $this->Html->link(__('New Translate String'), ['controller' => 'TranslateStrings', 'action' => 'add']) ?></li>
	</ul>
</nav>
<div class="translateGroups index col-sm-8 col-xs-12">
	<h3><?= __('Translate Groups') ?></h3>
	<table class="table table-striped">
		<thead>
			<tr>
				<th><?= $this->Paginator->sort('name') ?></th>
				<th><?= $this->Paginator->sort('active') ?></th>
				<th><?= $this->Paginator->sort('prio') ?></th>
				<th><?= $this->Paginator->sort('created', null, ['direction' => 'desc']) ?></th>
				<th><?= $this->Paginator->sort('modified', null, ['direction' => 'desc']) ?></th>
				<th class="actions"><?= __('Actions') ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($translateGroups as $translateGroup): ?>
			<tr>
				<td><?= h($translateGroup->name) ?></td>
				<td><?= $this->Format->yesNo($translateGroup->active) ?></td>
				<td><?= $this->Number->format($translateGroup->prio) ?></td>
				<td><?= $this->Time->nice($translateGroup->created) ?></td>
				<td><?= $this->Time->nice($translateGroup->modified) ?></td>
				<td class="actions">
				<?= $this->Html->link($this->Format->icon('view'), ['action' => 'view', $translateGroup->id], ['escape' => false]); ?>
				<?= $this->Html->link($this->Format->icon('edit'), ['action' => 'edit', $translateGroup->id], ['escape' => false]); ?>
				<?= $this->Form->postLink($this->Format->icon('delete'), ['action' => 'delete', $translateGroup->id], ['escape' => false, 'confirm' => __('Are you sure you want to delete # {0}?', $translateGroup->id)]); ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<?php echo $this->element('Tools.pagination'); ?>
</div>
