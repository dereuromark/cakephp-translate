<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateApiTranslation $translateApiTranslation
 */
?>
<nav class="actions col-md-3 col-sm-4 col-xs-12">
	<ul class="side-nav nav nav-pills nav-stacked">
		<li class="heading"><?= __('Actions') ?></li>
		<li><?= $this->Html->link(__('Edit Translate Api Translation'), ['action' => 'edit', $translateApiTranslation->id]) ?> </li>
		<li><?= $this->Form->postLink(__('Delete Translate Api Translation'), ['action' => 'delete', $translateApiTranslation->id], ['confirm' => __('Are you sure you want to delete # {0}?', $translateApiTranslation->id)]) ?> </li>
		<li><?= $this->Html->link(__('List Translate Api Translations'), ['action' => 'index']) ?> </li>
		<li><?= $this->Html->link(__('New Translate Api Translation'), ['action' => 'add']) ?> </li>
	</ul>
</nav>
<div class="translateApiTranslations view col-md-9 col-sm-8 col-xs-12">
	<h2><?= h($translateApiTranslation->id) ?></h2>
	<table class="table vertical-table">
		<tr>
			<th><?= __('From') ?></th>
			<td><?= h($translateApiTranslation->from) ?></td>
		</tr>
		<tr>
			<th><?= __('To') ?></th>
			<td><?= h($translateApiTranslation->to) ?></td>
		</tr>
		<tr>
			<th><?= __('Engine') ?></th>
			<td><?= h($translateApiTranslation->engine) ?></td>
		</tr>
		<tr>
			<th><?= __('Created') ?></th>
			<td><?= $this->Time->nice($translateApiTranslation->created) ?></td>
		</tr>
	</table>
	<div class="row">
		<h3><?= __('Key') ?></h3>
		<?= $this->Text->autoParagraph(h($translateApiTranslation->key)); ?>
	</div>
	<div class="row">
		<h3><?= __('Value') ?></h3>
		<?= $this->Text->autoParagraph(h($translateApiTranslation->value)); ?>
	</div>

</div>
