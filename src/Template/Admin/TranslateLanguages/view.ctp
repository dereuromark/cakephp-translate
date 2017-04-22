<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateLanguage $translateLanguage
 */
?>
<nav class="actions col-sm-4 col-xs-12">
	<ul class="side-nav nav nav-pills nav-stacked">
		<li class="heading"><?= __('Actions') ?></li>
		<li><?= $this->Html->link(__('Edit Translate Language'), ['action' => 'edit', $translateLanguage->id]) ?> </li>
		<li><?= $this->Form->postLink(__('Delete Translate Language'), ['action' => 'delete', $translateLanguage->id], ['confirm' => __('Are you sure you want to delete # {0}?', $translateLanguage->id)]) ?> </li>
		<li><?= $this->Html->link(__('List Translate Languages'), ['action' => 'index']) ?> </li>
		<li><?= $this->Html->link(__('New Translate Language'), ['action' => 'add']) ?> </li>
	</ul>
</nav>
<div class="translateLanguages view col-sm-8 col-xs-12">
	<h2><?= h($translateLanguage->name) ?></h2>
	<table class="table vertical-table">
		<tr>
			<th><?= __('Language Id') ?></th>
			<td><?= $this->Number->format($translateLanguage->language_id) ?></td>
		</tr>
		<tr>
			<th><?= __('Name') ?></th>
			<td><?= h($translateLanguage->name) ?></td>
		</tr>
		<tr>
			<th><?= __('Iso2') ?></th>
			<td><?= h($translateLanguage->iso2) ?></td>
		</tr>
		<tr>
			<th><?= __('Locale') ?></th>
			<td><?= h($translateLanguage->locale) ?></td>
		</tr>
		<tr>
			<th><?= __('Active') ?></th>
			<td><?= $this->Format->yesNo($translateLanguage->active) ?></td>
		</tr>
	</table>


</div>
