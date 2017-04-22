<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateLanguage[] $translateLanguages
 */
?>
<nav class="actions col-sm-4 col-xs-12">
	<ul class="side-nav nav nav-pills nav-stacked">
		<li class="heading"><?= __('Actions') ?></li>
		<li><?= $this->Html->link(__('Overview'), ['controller' => 'Translate', 'action' => 'index']) ?></li>
		<li><?= $this->Html->link(__('New Translate Language'), ['action' => 'add']) ?></li>
	</ul>
</nav>
<div class="translateLanguages index col-sm-8 col-xs-12">
	<h3><?= __('Translate Languages') ?></h3>
	<table class="table table-striped">
		<thead>
			<tr>
				<th><?= $this->Paginator->sort('language_id') ?></th>
				<th><?= $this->Paginator->sort('name') ?></th>
				<th><?= $this->Paginator->sort('locale') ?></th>
				<th><?= $this->Paginator->sort('active') ?></th>
				<th class="actions"><?= __('Actions') ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($translateLanguages as $translateLanguage): ?>
			<tr>
				<td>
<?php	if (!empty($translateLanguage->language['code'])) {
					echo $this->Translation->flag($translateLanguage->language['code']);
			} ?>

<?= $this->Number->format($translateLanguage->language_id) ?></td>
				<td><?= h($translateLanguage->name) ?></td>
				<td><?= h($translateLanguage->locale) ?></td>
				<td><?= $this->Format->yesNo($translateLanguage->active) ?></td>
				<td class="actions">
				<?= $this->Html->link($this->Format->icon('view'), ['action' => 'view', $translateLanguage->id], ['escape' => false]); ?>
				<?= $this->Html->link($this->Format->icon('edit'), ['action' => 'edit', $translateLanguage->id], ['escape' => false]); ?>
				<?= $this->Form->postLink($this->Format->icon('delete'), ['action' => 'delete', $translateLanguage->id], ['escape' => false, 'confirm' => __('Are you sure you want to delete # {0}?', $translateLanguage->id)]); ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<?php echo $this->element('Tools.pagination'); ?>
</div>
