<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateDomain $translateDomain
 */
?>
<nav class="actions col-md-3 col-sm-4 col-12">
	<ul class="nav nav-pills flex-column">
		<li class="heading"><?= __d('translate', 'Actions') ?></li>
		<li><?= $this->Html->link(__d('translate', 'Edit Translate Domain'), ['action' => 'edit', $translateDomain->id]) ?> </li>
		<li><?= $this->Form->postLink(__d('translate', 'Delete Translate Domain'), ['action' => 'delete', $translateDomain->id], ['confirm' => __d('translate', 'Are you sure you want to delete # {0}?', $translateDomain->id)]) ?> </li>
		<li><?= $this->Html->link(__d('translate', 'List Translate Domains'), ['action' => 'index']) ?> </li>
		<li><?= $this->Html->link(__d('translate', 'New Translate Domain'), ['action' => 'add']) ?> </li>
	</ul>
</nav>
<div class="translateDomains view col-md-9 col-sm-8 col-12">
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
			<td><?= $this->element('Translate.yes_no', ['value' => $translateDomain->active]) ?></td>
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
