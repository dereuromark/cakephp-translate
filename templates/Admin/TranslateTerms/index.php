<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateTerm[]|\Cake\Collection\CollectionInterface $translateTerms
 * @var mixed $_isSearch
 */

use Cake\Core\Plugin;

?>
<nav class="actions col-md-3 col-sm-4 col-xs-12">
	<ul class="side-nav nav nav-pills nav-stacked">
		<li class="heading"><?= __d('translate', 'Actions') ?></li>
		<li><?= $this->Html->link(__d('translate', 'New Translate Term'), ['action' => 'add']) ?></li>
		<li><?= $this->Html->link(__d('translate', 'List Translate Strings'), ['controller' => 'TranslateStrings', 'action' => 'index']) ?></li>
		<li><?= $this->Html->link(__d('translate', 'New Translate String'), ['controller' => 'TranslateStrings', 'action' => 'add']) ?></li>
		<li><?= $this->Html->link(__d('translate', 'List Translate Languages'), ['controller' => 'TranslateLanguages', 'action' => 'index']) ?></li>
		<li><?= $this->Html->link(__d('translate', 'New Translate Language'), ['controller' => 'TranslateLanguages', 'action' => 'add']) ?></li>
	</ul>
</nav>
<div class="translateTerms index col-md-9 col-sm-8 col-xs-12">
	<h2><?= __d('translate', 'Translate Terms') ?></h2>

	<?php
	echo $this->Form->create(null, ['valueSources' => 'query']);
	// You'll need to populate $authors in the template from your controller
	echo $this->Form->control('translate_language_id', ['empty' => ' - ' . __d('translate', 'noLimitation') . ' - ', 'label' => __d('translate', 'Language')]);
	echo $this->Form->control('search', ['placeholder' => '']);
	?>
	<div class="text-right" style="margin-bottom: 8px;">
		<?php
		echo $this->Form->button(__d('translate', 'Filter'), ['type' => 'submit']);
		if (!empty($_isSearch)) {
			echo ' ' . $this->Html->link(__d('translate', 'Reset'), ['action' => 'index'], ['class' => 'btn btn-default']);
		}
		?>
	</div>
	<?php
	echo $this->Form->end();
	?>

	<table class="table table-striped">
		<thead>
			<tr>
				<th><?= $this->Paginator->sort('translate_string_id', __('Text')) ?></th>
				<th><?= $this->Paginator->sort('content', __('Translation')) ?></th>
				<th><?= $this->Paginator->sort('translate_language_id', __('Language')) ?></th>
				<th><?= $this->Paginator->sort('confirmed') ?></th>
				<th><?= $this->Paginator->sort('created', null, ['direction' => 'desc']) ?></th>
				<th><?= $this->Paginator->sort('modified', null, ['direction' => 'desc']) ?></th>
				<th class="actions"><?= __d('translate', 'Actions') ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($translateTerms as $translateTerm): ?>
			<tr>
				<td>
					<?= $translateTerm->has('translate_string') ? $this->Html->link($this->Text->truncate($translateTerm->translate_string->name), ['controller' => 'TranslateStrings', 'action' => 'view', $translateTerm->translate_string->id]) : '' ?>

					<?php if ($translateTerm->comment) {
						echo $this->Format->icon('comment', ['title' => $translateTerm->comment]);
					} ?>
				</td>
				<td>
					<?php echo $this->Text->truncate($translateTerm->content); ?>
				</td>
				<td><?= $translateTerm->has('translate_language') ? $this->Html->link($translateTerm->translate_language->name, ['controller' => 'TranslateLanguages', 'action' => 'view', $translateTerm->translate_language->id]) : '' ?></td>
				<td><?= $this->Format->yesNo($translateTerm->confirmed) ?>
					<div>
						<small><?= h($translateTerm->confirmed_by) ?></small>
					</div>
				</td>
				<td><?= $this->Time->nice($translateTerm->created) ?></td>
				<td><?= $this->Time->nice($translateTerm->modified) ?></td>
				<td class="actions">
				<?= $this->Html->link($this->Format->icon('view'), ['action' => 'view', $translateTerm->id], ['escape' => false]); ?>
				<?= $this->Html->link($this->Format->icon('edit'), ['action' => 'edit', $translateTerm->id], ['escape' => false]); ?>
				<?= $this->Form->postLink($this->Format->icon('delete'), ['action' => 'delete', $translateTerm->id], ['escape' => false, 'confirm' => __d('translate', 'Are you sure you want to delete # {0}?', $translateTerm->id)]); ?>
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
