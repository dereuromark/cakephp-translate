<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateTerm $translateTerm
 */
?>
<nav class="actions col-md-3 col-sm-4 col-xs-12">
	<ul class="side-nav nav nav-pills nav-stacked">
		<li class="heading"><?= __d('translate', 'Actions') ?></li>
		<li><?= $this->Html->link(__d('translate', 'Edit Translate Term'), ['action' => 'edit', $translateTerm->id]) ?> </li>
		<li><?= $this->Form->postLink(__d('translate', 'Delete Translate Term'), ['action' => 'delete', $translateTerm->id], ['confirm' => __d('translate', 'Are you sure you want to delete # {0}?', $translateTerm->id)]) ?> </li>
		<li><?= $this->Html->link(__d('translate', 'List Translate Strings'), ['controller' => 'TranslateStrings', 'action' => 'index']) ?> </li>
		<li><?= $this->Html->link(__d('translate', 'New Translate String'), ['controller' => 'TranslateStrings', 'action' => 'add']) ?> </li>
	</ul>
</nav>
<div class="translateTerms view col-md-9 col-sm-8 col-xs-12">
	<h2><?= h($translateTerm->content) ?></h2>
	<table class="table vertical-table">
		<tr>
			<th><?= __d('translate', 'Translate String') ?></th>
			<td><?= $translateTerm->has('translate_string') ? $this->Html->link($translateTerm->translate_string->name, ['controller' => 'TranslateStrings', 'action' => 'view', $translateTerm->translate_string->id]) : '' ?></td>
		</tr>
		<tr>
			<th><?= __d('translate', 'Comment') ?></th>
			<td><?= h($translateTerm->comment) ?></td>
		</tr>
		<tr>
			<th><?= __d('translate', 'Translate Language') ?></th>
			<td><?= $translateTerm->has('translate_language') ? $this->Html->link($translateTerm->translate_language->name, ['controller' => 'TranslateLanguages', 'action' => 'view', $translateTerm->translate_language->id]) : '' ?></td>
		</tr>
		<tr>
			<th><?= __d('translate', 'User Id') ?></th>
			<td><?= h($translateTerm->user_id) ?></td>
		</tr>
		<tr>
			<th><?= __d('translate', 'Confirmed') ?></th>
			<td><?= $this->element('Translate.yes_no', ['value' => $translateTerm->confirmed]) ?></td>
		</tr>
		<tr>
			<th><?= __d('translate', 'Confirmed By') ?></th>
			<td><?= h($translateTerm->confirmed_by) ?></td>
		</tr>
		<tr>
			<th><?= __d('translate', 'Created') ?></th>
			<td><?= $this->Time->nice($translateTerm->created) ?></td>
		</tr>
		<tr>
			<th><?= __d('translate', 'Modified') ?></th>
			<td><?= $this->Time->nice($translateTerm->modified) ?></td>
		</tr>
	</table>
	<div class="row">
		<h3><?= __d('translate', 'Content') ?></h3>
		<?= $this->Text->autoParagraph(h($translateTerm->content)); ?>
	</div>

</div>
