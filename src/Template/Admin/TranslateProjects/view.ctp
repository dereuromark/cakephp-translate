<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateProject $translateProject
 */
?>
<nav class="actions col-sm-4 col-xs-12">
	<ul class="side-nav nav nav-pills nav-stacked">
		<li class="heading"><?= __d('translate', 'Actions') ?></li>
		<li><?= $this->Html->link(__d('translate', 'Edit Translate Project'), ['action' => 'edit', $translateProject->id]) ?> </li>
		<li><?= $this->Form->postLink(__d('translate', 'Delete Translate Project'), ['action' => 'delete', $translateProject->id], ['confirm' => __d('translate', 'Are you sure you want to delete # {0}?', $translateProject->id)]) ?> </li>
		<li><?= $this->Html->link(__d('translate', 'List Translate Projects'), ['action' => 'index']) ?> </li>
		<li><?= $this->Html->link(__d('translate', 'New Translate Project'), ['action' => 'add']) ?> </li>
		<li><?= $this->Html->link(__d('translate', 'List Translate Groups'), ['controller' => 'TranslateDomains', 'action' => 'index']) ?> </li>
		<li><?= $this->Html->link(__d('translate', 'New Translate Group'), ['controller' => 'TranslateDomains', 'action' => 'add']) ?> </li>
	</ul>
</nav>
<div class="translateProjects view col-sm-8 col-xs-12">
	<h2><?= h($translateProject->name) ?></h2>
	<table class="table vertical-table">
		<tr>
			<th><?= __d('translate', 'Name') ?></th>
			<td><?= h($translateProject->name) ?></td>
		</tr>
		<tr>
			<th><?= __d('translate', 'Type') ?></th>
			<td><?= $this->Number->format($translateProject->type) ?></td>
		</tr>
		<tr>
			<th><?= __d('translate', 'Default') ?></th>
			<td><?= $this->Format->yesNo($translateProject->default) ?></td>
		</tr>
		<tr>
			<th><?= __d('translate', 'Status') ?></th>
			<td><?= $this->Number->format($translateProject->status) ?></td>
		</tr>
		<tr>
			<th><?= __d('translate', 'Created') ?></th>
			<td><?= $this->Time->nice($translateProject->created) ?></td>
		</tr>
		<tr>
			<th><?= __d('translate', 'Modified') ?></th>
			<td><?= $this->Time->nice($translateProject->modified) ?></td>
		</tr>
	</table>

	<div class="related">
		<h3><?= __d('translate', 'Related Translate Groups') ?></h3>
		<?php if (!empty($translateProject->translate_domains)): ?>
		<table class="table table-horizontal">
									<tr>
			<th><?= __d('translate', 'Name') ?></th>
						<tr>
			<th><?= __d('translate', 'Project Id') ?></th>
						<tr>
			<th><?= __d('translate', 'Active') ?></th>
						<tr>
			<th><?= __d('translate', 'Prio') ?></th>
						<tr>
			<th><?= __d('translate', 'Created') ?></th>
						<tr>
			<th><?= __d('translate', 'Modified') ?></th>
				<th class="actions"><?= __d('translate', 'Actions') ?></th>
			</tr>
			<?php foreach ($translateProject->translate_domains as $translateDomains): ?>
			<tr>
				<td><?= h($translateDomains->id) ?></td>
				<td><?= h($translateDomains->name) ?></td>
				<td><?= h($translateDomains->project_id) ?></td>
				<td><?= h($translateDomains->active) ?></td>
				<td><?= h($translateDomains->prio) ?></td>
				<td><?= h($translateDomains->created) ?></td>
				<td><?= h($translateDomains->modified) ?></td>
				<td class="actions">
					<?= $this->Html->link(__d('translate', 'View'), ['controller' => 'TranslateDomains', 'action' => 'view', $translateDomains->id]) ?>

					<?= $this->Html->link(__d('translate', 'Edit'), ['controller' => 'TranslateDomains', 'action' => 'edit', $translateDomains->id]) ?>

					<?= $this->Form->postLink(__d('translate', 'Delete'), ['controller' => 'TranslateDomains', 'action' => 'delete', $translateDomains->id], ['confirm' => __d('translate', 'Are you sure you want to delete # {0}?', $translateDomains->id)]) ?>

				</td>
			</tr>
			<?php endforeach; ?>
		</table>
	<?php endif; ?>
	</div>
</div>
