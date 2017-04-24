<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateDomain $translateDomain
 */
?>
<nav class="actions col-sm-4 col-xs-12">
	<ul class="side-nav nav nav-pills nav-stacked">
		<li class="heading"><?= __d('translate', 'Actions') ?></li>
		<li><?= $this->Html->link(__d('translate', 'Edit Translate Group'), ['action' => 'edit', $translateDomain->id]) ?> </li>
		<li><?= $this->Form->postLink(__d('translate', 'Delete Translate Group'), ['action' => 'delete', $translateDomain->id], ['confirm' => __d('translate', 'Are you sure you want to delete # {0}?', $translateDomain->id)]) ?> </li>
		<li><?= $this->Html->link(__d('translate', 'List Translate Groups'), ['action' => 'index']) ?> </li>
		<li><?= $this->Html->link(__d('translate', 'New Translate Group'), ['action' => 'add']) ?> </li>
	</ul>
</nav>
<div class="translateDomains view col-sm-8 col-xs-12">
	<h2><?= h($translateDomain->name) ?></h2>
	<table class="table vertical-table">
		<tr>
			<th><?= __d('translate', 'Name') ?></th>
			<td><?= h($translateDomain->name) ?></td>
		</tr>
		<tr>
			<th><?= __d('translate', 'Translate Project') ?></th>
			<td><?= $translateDomain->has('translate_project') ? $this->Html->link($translateDomain->translate_project->name, ['controller' => 'TranslateProjects', 'action' => 'view', $translateDomain->translate_project->id]) : '' ?></td>
		</tr>
		<tr>
			<th><?= __d('translate', 'Active') ?></th>
			<td><?= $this->Format->yesNo($translateDomain->active) ?></td>
		</tr>
		<tr>
			<th><?= __d('translate', 'Path') ?></th>
			<td><?= h($translateDomain->path) ?></td>
		</tr>
		<tr>
			<th><?= __d('translate', 'Prio') ?></th>
			<td><?= $this->Number->format($translateDomain->prio) ?></td>
		</tr>
		<tr>
			<th><?= __d('translate', 'Created') ?></th>
			<td><?= $this->Time->nice($translateDomain->created) ?></td>
		</tr>
		<tr>
			<th><?= __d('translate', 'Modified') ?></th>
			<td><?= $this->Time->nice($translateDomain->modified) ?></td>
		</tr>
	</table>


</div>
