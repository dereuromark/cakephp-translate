<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateApiTranslation[]|\Cake\Collection\CollectionInterface $translateApiTranslations
 */
?>
<nav class="actions col-md-3 col-sm-4 col-xs-12">
	<ul class="side-nav nav nav-pills nav-stacked">
		<li class="heading"><?= __('Actions') ?></li>
		<li><?= $this->Html->link(__('New Translate Api Translation'), ['action' => 'add']) ?></li>
	</ul>
</nav>
<div class="translateApiTranslations index col-md-9 col-sm-8 col-xs-12">
	<h2><?= __('Translate Api Translations') ?></h2>
	<table class="table table-striped">
		<thead>
			<tr>
				<th><?= $this->Paginator->sort('key') ?></th>
				<th><?= $this->Paginator->sort('from') ?></th>
				<th><?= $this->Paginator->sort('to') ?></th>
				<th><?= $this->Paginator->sort('engine') ?></th>
				<th><?= $this->Paginator->sort('created', null, ['direction' => 'desc']) ?></th>
				<th class="actions"><?= __('Actions') ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($translateApiTranslations as $translateApiTranslation): ?>
			<tr>
				<td><?= h($this->Text->truncate($translateApiTranslation->key)) ?></td>
				<td><?= h($translateApiTranslation->from) ?></td>
				<td><?= h($translateApiTranslation->to) ?></td>
				<td><?= h($translateApiTranslation->engine) ?></td>
				<td><?= $this->Time->nice($translateApiTranslation->created) ?></td>
				<td class="actions">
				<?= $this->Html->link($this->Format->icon('view'), ['action' => 'view', $translateApiTranslation->id], ['escape' => false]); ?>
				<?= $this->Html->link($this->Format->icon('edit'), ['action' => 'edit', $translateApiTranslation->id], ['escape' => false]); ?>
				<?= $this->Form->postLink($this->Format->icon('delete'), ['action' => 'delete', $translateApiTranslation->id], ['escape' => false, 'confirm' => __('Are you sure you want to delete # {0}?', $translateApiTranslation->id)]); ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<?php echo $this->element('Tools.pagination'); ?>
</div>
