<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateLanguage[]|\Cake\Collection\CollectionInterface $translateLanguages
 */

use Cake\Core\Plugin;

?>
<nav class="actions col-md-3 col-sm-4 col-xs-12">
	<ul class="side-nav nav nav-pills nav-stacked">
		<li class="heading"><?= __d('translate', 'Actions') ?></li>
		<li><?= $this->Html->link(__d('translate', 'Overview'), ['controller' => 'Translate', 'action' => 'index']) ?></li>
		<li><?= $this->Html->link(__d('translate', 'From Locale'), ['action' => 'fromLocale']) ?></li>
		<li><?= $this->Html->link(__d('translate', 'To Locale'), ['action' => 'toLocale']) ?></li>
		<li><?= $this->Html->link(__d('translate', 'New Translate Language'), ['action' => 'add']) ?></li>
	</ul>
</nav>
<div class="translateLanguages index col-md-9 col-sm-8 col-xs-12">
	<h2><?= __d('translate', 'Translate Languages') ?></h2>
	<table class="table table-striped">
		<thead>
			<tr>
				<th><?= $this->Paginator->sort('language_id') ?></th>
				<th><?= $this->Paginator->sort('name') ?></th>
				<th><?= $this->Paginator->sort('locale') ?></th>
				<th><?= $this->Paginator->sort('active') ?></th>
				<th class="actions"><?= __d('translate', 'Actions') ?></th>
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
				<?= $this->Form->postLink($this->Format->icon('delete'), ['action' => 'delete', $translateLanguage->id], ['escape' => false, 'confirm' => __d('translate', 'Are you sure you want to delete # {0}?', $translateLanguage->id)]); ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<?php
	if (Plugin::isLoaded('Tools')) {
		echo $this->element('Tools.pagination');
	} else {
		echo $this->element('pagination');
	}
	?>
</div>

