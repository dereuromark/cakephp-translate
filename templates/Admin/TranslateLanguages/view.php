<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateLanguage $translateLanguage
 */
?>
<nav class="actions col-md-3 col-sm-4 col-xs-12">
	<ul class="side-nav nav nav-pills nav-stacked">
		<li class="heading"><?= __d('translate', 'Actions') ?></li>
		<li><?= $this->Html->link(__d('translate', 'Edit Translate Language'), ['action' => 'edit', $translateLanguage->id]) ?> </li>
		<li><?= $this->Form->postLink(__d('translate', 'Delete Translate Language'), ['action' => 'delete', $translateLanguage->id], ['confirm' => __d('translate', 'Are you sure you want to delete # {0}?', $translateLanguage->id)]) ?> </li>
		<li><?= $this->Html->link(__d('translate', 'List Translate Languages'), ['action' => 'index']) ?> </li>
		<li><?= $this->Html->link(__d('translate', 'New Translate Language'), ['action' => 'add']) ?> </li>
	</ul>
</nav>
<div class="translateLanguages view col-md-9 col-sm-8 col-xs-12">
	<h2><?= h($translateLanguage->name) ?></h2>
	<table class="table vertical-table">
		<tr>
			<th><?= __d('translate', 'Language Relation') ?></th>
			<td><?= $translateLanguage->language_id ? h($translateLanguage->language->name) : '-' ?></td>
		</tr>
		<tr>
			<th><?= __d('translate', 'Name') ?></th>
			<td><?= h($translateLanguage->name) ?></td>
		</tr>
		<tr>
			<th><?= __d('translate', 'Locale') ?></th>
			<td><?= h($translateLanguage->locale) ?></td>
		</tr>
		<tr>
			<th><?= __d('translate', 'Iso2') ?></th>
			<td><?= h($translateLanguage->iso2) ?></td>
		</tr>
		<tr>
			<th><?= __d('translate', 'Active') ?></th>
			<td><?= $this->element('Translate.yes_no', ['value' => $translateLanguage->active]) ?></td>
		</tr>
	</table>


</div>
