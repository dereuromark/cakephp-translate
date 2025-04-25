<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\Translate\Model\Entity\TranslateString> $translateStrings
 * @var bool $_isSearch
 */

use Cake\Core\Plugin;

?>
<nav class="actions col-md-3 col-sm-4 col-xs-12">
	<ul class="side-nav nav nav-pills nav-stacked">
		<li class="heading"><?= __d('translate', 'Actions') ?></li>
		<li><?= $this->Html->link(__d('translate', 'Overview'), ['controller' => 'Translate', 'action' => 'index']) ?></li>
		<li><?= $this->Html->link(__d('translate', 'New Translate String'), ['action' => 'add']) ?></li>
	</ul>
</nav>
<div class="translateStrings index col-md-9 col-sm-8 col-xs-12">
	<h2><?= __d('translate', 'Translate Strings') ?></h2>

	<?php
	echo $this->Form->create(null, ['valueSources' => 'query']);
	// You'll need to populate $authors in the template from your controller
	echo $this->Form->control('translate_domain_id', ['empty' => ' - ' . __d('translate', 'noLimitation') . ' - ']);
	echo $this->Form->control('search', ['placeholder' => '']);
	echo $this->Form->control('missing_translation', ['type' => 'checkbox', 'hiddenField' => '']);

	?>
	<div class="text-right" style="margin-bottom: 8px;">
		<?php
		echo $this->Form->button(__d('translate', 'Filter'), ['type' => 'submit']);
		if (!empty($_isSearch)) {
			echo ' ' . $this->Html->link(__d('translate', 'Reset'), ['action' => 'index'], ['class' => 'btn btn-default']);
		}
		?>

		<p><small>Please note that name/context are case sensitive by default!</small></p>
	</div>
	<?php
	echo $this->Form->end();
	?>

	<table class="table table-striped">
		<thead>
			<tr>
				<th><?php echo $this->Paginator->sort('name');?></th>
				<th><?= $this->Paginator->sort('active') ?></th>
				<th><?= $this->Paginator->sort('is_html') ?></th>
				<th><?= $this->Paginator->sort('plural') ?></th>
				<th><?= $this->Paginator->sort('context') ?></th>
				<th><?= $this->Paginator->sort('last_import', null, ['direction' => 'desc']) ?></th>
				<th><?= $this->Paginator->sort('created', null, ['direction' => 'desc']) ?></th>
				<th><?= $this->Paginator->sort('modified', null, ['direction' => 'desc']) ?></th>
				<th class="actions"><?= __d('translate', 'Actions') ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($translateStrings as $translateString): ?>
			<tr>
				<td>
					<span class="badge badge-primary bg-dark"><?php echo h($translateString->translate_domain->name); ?></span>

					<?php echo h($this->Text->truncate($translateString['name'])); ?>
				</td>
				<td><?= $this->element('Translate.yes_no', ['value' => $translateString->is_html]) ?></td>
				<td><?= $this->element('Translate.yes_no', ['value' => $translateString->active]) ?></td>
				<td><?= $this->element('Translate.yes_no', ['value' => $translateString->plural, 'title' => $translateString->plural]) ?></td>
				<td><?= $this->element('Translate.yes_no', ['value' => $translateString->context, 'title' => $translateString->context]) ?></td>
				<td><?= $this->Time->nice($translateString->last_import) ?></td>
				<td><?= $this->Time->nice($translateString->created) ?></td>
				<td><?= $this->Time->nice($translateString->modified) ?></td>
				<td class="actions">
				<?php echo $this->Html->link($this->Icon->render('translate'), ['action'=>'translate', $translateString['id']], ['escape'=>false]); ?>
				<?= $this->Html->link($this->Icon->render('view'), ['action' => 'view', $translateString->id], ['escape' => false]); ?>
				<?= $this->Html->link($this->Icon->render('edit'), ['action' => 'edit', $translateString->id], ['escape' => false]); ?>
				<?= $this->Form->postLink($this->Icon->render('delete'), ['action' => 'delete', $translateString->id], ['escape' => false, 'confirm' => __d('translate', 'Are you sure you want to delete # {0}?', $translateString->id)]); ?>
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
