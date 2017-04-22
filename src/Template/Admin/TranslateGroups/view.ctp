<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateGroup $translateGroup
 */
?>
<nav class="actions col-sm-4 col-xs-12">
	<ul class="side-nav nav nav-pills nav-stacked">
		<li class="heading"><?= __('Actions') ?></li>
		<li><?= $this->Html->link(__('Edit Translate Group'), ['action' => 'edit', $translateGroup->id]) ?> </li>
		<li><?= $this->Form->postLink(__('Delete Translate Group'), ['action' => 'delete', $translateGroup->id], ['confirm' => __('Are you sure you want to delete # {0}?', $translateGroup->id)]) ?> </li>
		<li><?= $this->Html->link(__('List Translate Groups'), ['action' => 'index']) ?> </li>
		<li><?= $this->Html->link(__('New Translate Group'), ['action' => 'add']) ?> </li>
		<li><?= $this->Html->link(__('List Translate Projects'), ['controller' => 'TranslateProjects', 'action' => 'index']) ?> </li>
		<li><?= $this->Html->link(__('New Translate Project'), ['controller' => 'TranslateProjects', 'action' => 'add']) ?> </li>
		<li><?= $this->Html->link(__('List Translate Strings'), ['controller' => 'TranslateStrings', 'action' => 'index']) ?> </li>
		<li><?= $this->Html->link(__('New Translate String'), ['controller' => 'TranslateStrings', 'action' => 'add']) ?> </li>
	</ul>
</nav>
<div class="translateGroups view col-sm-8 col-xs-12">
	<h2><?= h($translateGroup->name) ?></h2>
	<table class="table vertical-table">
		<tr>
			<th><?= __('Name') ?></th>
			<td><?= h($translateGroup->name) ?></td>
		</tr>
		<tr>
			<th><?= __('Translate Project') ?></th>
			<td><?= $translateGroup->has('translate_project') ? $this->Html->link($translateGroup->translate_project->name, ['controller' => 'TranslateProjects', 'action' => 'view', $translateGroup->translate_project->id]) : '' ?></td>
		</tr>
		<tr>
			<th><?= __('Active') ?></th>
			<td><?= $this->Format->yesNo($translateGroup->active) ?></td>
		</tr>
		<tr>
			<th><?= __('Prio') ?></th>
			<td><?= $this->Number->format($translateGroup->prio) ?></td>
		</tr>
		<tr>
			<th><?= __('Created') ?></th>
			<td><?= $this->Time->nice($translateGroup->created) ?></td>
		</tr>
		<tr>
			<th><?= __('Modified') ?></th>
			<td><?= $this->Time->nice($translateGroup->modified) ?></td>
		</tr>
	</table>


</div>
