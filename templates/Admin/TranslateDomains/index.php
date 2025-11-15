<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\Translate\Model\Entity\TranslateDomain> $translateDomains
 */

use Cake\Core\Plugin;

?>
<nav class="actions col-md-3 col-sm-4 col-12">
	<ul class="nav flex-column nav-pills">
		<li class="nav-item">
			<span class="nav-link disabled fw-bold"><?= __d('translate', 'Actions') ?></span>
		</li>
		<li class="nav-item">
			<?= $this->Html->link(__d('translate', 'Overview'), ['controller' => 'Translate', 'action' => 'index'], ['class' => 'nav-link']) ?>
		</li>
		<li class="nav-item">
			<?= $this->Html->link(__d('translate', 'New Translate Domain'), ['action' => 'add'], ['class' => 'nav-link']) ?>
		</li>
		<li class="nav-item">
			<?= $this->Html->link(__d('translate', 'List Translate Projects'), ['controller' => 'TranslateProjects', 'action' => 'index'], ['class' => 'nav-link']) ?>
		</li>
		<li class="nav-item">
			<?= $this->Html->link(__d('translate', 'New Translate Project'), ['controller' => 'TranslateProjects', 'action' => 'add'], ['class' => 'nav-link']) ?>
		</li>
		<li class="nav-item">
			<?= $this->Html->link(__d('translate', 'List Translate Strings'), ['controller' => 'TranslateStrings', 'action' => 'index'], ['class' => 'nav-link']) ?>
		</li>
		<li class="nav-item">
			<?= $this->Html->link(__d('translate', 'New Translate String'), ['controller' => 'TranslateStrings', 'action' => 'add'], ['class' => 'nav-link']) ?>
		</li>
	</ul>
</nav>

<div class="translateDomains index col-md-9 col-sm-8 col-12">
	<h2><?= __d('translate', 'Translate Domains') ?></h2>
	<table class="table table-striped">
		<thead>
			<tr>
				<th><?= $this->Paginator->sort('name') ?></th>
				<th><?= $this->Paginator->sort('active') ?></th>
				<th><?= $this->Paginator->sort('path') ?></th>
				<th><?= $this->Paginator->sort('created', null, ['direction' => 'desc']) ?></th>
				<th><?= $this->Paginator->sort('modified', null, ['direction' => 'desc']) ?></th>
				<th class="actions"><?= __d('translate', 'Actions') ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($translateDomains as $translateDomain) : ?>
			<tr>
				<td><?= h($translateDomain->name) ?></td>
				<td><?= $this->element('Translate.yes_no', ['value' => $translateDomain->active]) ?></td>
				<td><?= $this->element('Translate.yes_no', ['value' => (bool)$translateDomain->path]) ?></td>
				<td><?= $this->Time->nice($translateDomain->created) ?></td>
				<td><?= $this->Time->nice($translateDomain->modified) ?></td>
				<td class="actions">
				<?= $this->Html->link($this->Icon->render('view'), ['action' => 'view', $translateDomain->id], ['escape' => false]); ?>
				<?= $this->Html->link($this->Icon->render('edit'), ['action' => 'edit', $translateDomain->id], ['escape' => false]); ?>
				<?= $this->Form->postLink($this->Icon->render('delete'), ['action' => 'delete', $translateDomain->id], ['escape' => false, 'confirm' => __d('translate', 'Are you sure you want to delete # {0}?', $translateDomain->id)]); ?>
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
