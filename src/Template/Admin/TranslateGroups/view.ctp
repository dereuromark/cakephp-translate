<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateGroup $translateGroup
 */
?>
<nav class="actions col-sm-4 col-xs-12">
	<ul class="side-nav nav nav-pills nav-stacked">
		<li class="heading"><?= __d('translate', 'Actions') ?></li>
		<li><?= $this->Html->link(__d('translate', 'Edit Translate Group'), ['action' => 'edit', $translateGroup->id]) ?> </li>
		<li><?= $this->Form->postLink(__d('translate', 'Delete Translate Group'), ['action' => 'delete', $translateGroup->id], ['confirm' => __d('translate', 'Are you sure you want to delete # {0}?', $translateGroup->id)]) ?> </li>
		<li><?= $this->Html->link(__d('translate', 'List Translate Groups'), ['action' => 'index']) ?> </li>
		<li><?= $this->Html->link(__d('translate', 'New Translate Group'), ['action' => 'add']) ?> </li>
		<li><?= $this->Html->link(__d('translate', 'List Translate Projects'), ['controller' => 'TranslateProjects', 'action' => 'index']) ?> </li>
		<li><?= $this->Html->link(__d('translate', 'New Translate Project'), ['controller' => 'TranslateProjects', 'action' => 'add']) ?> </li>
		<li><?= $this->Html->link(__d('translate', 'List Translate Strings'), ['controller' => 'TranslateStrings', 'action' => 'index']) ?> </li>
		<li><?= $this->Html->link(__d('translate', 'New Translate String'), ['controller' => 'TranslateStrings', 'action' => 'add']) ?> </li>
	</ul>
</nav>
<div class="translateGroups view col-sm-8 col-xs-12">
	<h2><?= h($translateGroup->name) ?></h2>
	<table class="table vertical-table">
		<tr>
			<th><?= __d('translate', 'Name') ?></th>
			<td><?= h($translateGroup->name) ?></td>
		</tr>
		<tr>
			<th><?= __d('translate', 'Translate Project') ?></th>
			<td><?= $translateGroup->has('translate_project') ? $this->Html->link($translateGroup->translate_project->name, ['controller' => 'TranslateProjects', 'action' => 'view', $translateGroup->translate_project->id]) : '' ?></td>
		</tr>
		<tr>
			<th><?= __d('translate', 'Active') ?></th>
			<td><?= $this->Format->yesNo($translateGroup->active) ?></td>
		</tr>
		<tr>
			<th><?= __d('translate', 'Prio') ?></th>
			<td><?= $this->Number->format($translateGroup->prio) ?></td>
		</tr>
		<tr>
			<th><?= __d('translate', 'Created') ?></th>
			<td><?= $this->Time->nice($translateGroup->created) ?></td>
		</tr>
		<tr>
			<th><?= __d('translate', 'Modified') ?></th>
			<td><?= $this->Time->nice($translateGroup->modified) ?></td>
		</tr>
	</table>


</div>
