<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateProject[] $translateProjects
 */
?>
<nav class="actions col-md-3 col-sm-4 col-xs-12">
	<ul class="side-nav nav nav-pills nav-stacked">
		<li class="heading"><?= __d('translate', 'Actions') ?></li>
		<li><?= $this->Html->link(__d('translate', 'Overview'), ['controller' => 'Translate', 'action' => 'index']) ?></li>
		<li><?= $this->Html->link(__d('translate', 'New Translate Project'), ['action' => 'add']) ?></li>
		<li><?= $this->Html->link(__d('translate', 'List Translate Languages'), ['controller' => 'TranslateLanguages', 'action' => 'index']) ?> </li>
		<li><?= $this->Html->link(__d('translate', 'List Translate Domains'), ['controller' => 'TranslateDomains', 'action' => 'index']) ?> </li>
		<li><?= $this->Html->link(__d('translate', 'List Translate Strings'), ['controller' => 'TranslateStrings', 'action' => 'index']) ?></li>
	</ul>
</nav>
<div class="translateProjects index col-md-9 col-sm-8 col-xs-12">
	<h2><?= __d('translate', 'Translate Projects') ?></h2>
	<table class="table table-striped">
		<thead>
			<tr>
				<th><?= $this->Paginator->sort('name') ?></th>
				<th><?= $this->Paginator->sort('type') ?></th>
				<th><?= $this->Paginator->sort('default') ?></th>
				<th><?= $this->Paginator->sort('status') ?></th>
				<th><?= $this->Paginator->sort('created', null, ['direction' => 'desc']) ?></th>
				<th><?= $this->Paginator->sort('modified', null, ['direction' => 'desc']) ?></th>
				<th class="actions"><?= __d('translate', 'Actions') ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($translateProjects as $translateProject): ?>
			<tr>
				<td><?= h($translateProject->name) ?></td>
				<td><?= $translateProject::types($translateProject->type) ?></td>
				<td><?= $this->Format->yesNo($translateProject->default) ?></td>
				<td><?= $translateProject::statuses($translateProject->status) ?></td>
				<td><?= $this->Time->nice($translateProject->created) ?></td>
				<td><?= $this->Time->nice($translateProject->modified) ?></td>
				<td class="actions">
				<?= $this->Html->link($this->Format->icon('view'), ['action' => 'view', $translateProject->id], ['escape' => false]); ?>
				<?= $this->Html->link($this->Format->icon('edit'), ['action' => 'edit', $translateProject->id], ['escape' => false]); ?>
				<?= $this->Form->postLink($this->Format->icon('delete'), ['action' => 'delete', $translateProject->id], ['escape' => false, 'confirm' => __d('translate', 'Are you sure you want to delete # {0}?', $translateProject->id)]); ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<?php echo $this->element('Tools.pagination'); ?>
</div>
