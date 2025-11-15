<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateLocale $translateLocale
 */
?>
<nav class="actions col-md-3 col-sm-4 col-12">
	<ul class="nav nav-pills flex-column">
		<li class="heading"><?= __d('translate', 'Actions') ?></li>
		<li><?= $this->Html->link(__d('translate', 'Edit Locale'), ['action' => 'edit', $translateLocale->id]) ?> </li>
		<li><?= $this->Form->postLink(__d('translate', 'Delete Locale'), ['action' => 'delete', $translateLocale->id], ['confirm' => __d('translate', 'Are you sure you want to delete # {0}?', $translateLocale->id)]) ?> </li>
		<li><?= $this->Html->link(__d('translate', 'List Locales'), ['action' => 'index']) ?> </li>
		<li><?= $this->Html->link(__d('translate', 'New Locale'), ['action' => 'add']) ?> </li>
	</ul>
</nav>
<div class="translateLocales view col-md-9 col-sm-8 col-12">
	<h2><?= h($translateLocale->name) ?></h2>
	<table class="table vertical-table">
		<tr>
			<th><?= __d('translate', 'Language Relation') ?></th>
			<td><?= $translateLocale->language_id && !empty($translateLocale->language) ? h($translateLocale->language->name) : ($translateLocale->language_id ?: '-') ?></td>
		</tr>
		<tr>
			<th><?= __d('translate', 'Name') ?></th>
			<td><?= h($translateLocale->name) ?></td>
		</tr>
		<tr>
			<th><?= __d('translate', 'Locale') ?></th>
			<td><?= h($translateLocale->locale) ?></td>
		</tr>
		<tr>
			<th><?= __d('translate', 'Iso2') ?></th>
			<td><?= h($translateLocale->iso2) ?></td>
		</tr>
		<tr>
			<th><?= __d('translate', 'Active') ?></th>
			<td><?= $this->element('Translate.yes_no', ['value' => $translateLocale->active]) ?></td>
		</tr>
	</table>


</div>
