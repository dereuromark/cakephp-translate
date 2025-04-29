<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateString $translateString
 */

use Cake\Core\Configure;

?>
<nav class="actions col-md-3 col-sm-4 col-xs-12">
	<ul class="side-nav nav nav-pills nav-stacked">
		<li class="heading"><?= __d('translate', 'Actions') ?></li>
		<li><?= $this->Html->link(__d('translate', 'Edit Translate String'), ['action' => 'edit', $translateString->id]) ?> </li>
		<li><?= $this->Form->postLink(__d('translate', 'Delete Translate String'), ['action' => 'delete', $translateString->id], ['confirm' => __d('translate', 'Are you sure you want to delete # {0}?', $translateString->id)]) ?> </li>
		<li><?= $this->Html->link(__d('translate', 'List Translate Strings'), ['action' => 'index']) ?> </li>
		<li><?= $this->Html->link(__d('translate', 'New Translate String'), ['action' => 'add']) ?> </li>
	</ul>
</nav>
<div class="translateStrings view col-md-9 col-sm-8 col-xs-12">
	<h2><?= h($this->Text->truncate($translateString->name), 200) ?></h2>

	<pre>
	<?= $this->Text->autoParagraph(h($translateString->name)); ?>
	</pre>

	<table class="table vertical-table">
		<tr>
			<th><?= __d('translate', 'User') ?></th>
			<td><?= $translateString->has('user') ? $this->Html->link($translateString->user->email, ['controller' => 'Users', 'action' => 'view', $translateString->user->id]) : '' ?></td>
		</tr>
		<tr>
			<th><?= __d('translate', 'Active') ?></th>
			<td><?= $this->element('Translate.yes_no', ['value' => $translateString->active]) ?></td>
		</tr>
		<tr>
			<th><?= __d('translate', 'Is Html') ?></th>
			<td><?= $this->element('Translate.yes_no', ['value' => $translateString->is_html]) ?></td>
		</tr>
		<tr>
			<th><?= __d('translate', 'Last Import') ?></th>
			<td><?= $this->Time->nice($translateString->last_import) ?></td>
		</tr>
		<tr>
			<th><?= __d('translate', 'Created') ?></th>
			<td><?= $this->Time->nice($translateString->created) ?></td>
		</tr>
		<tr>
			<th><?= __d('translate', 'Modified') ?></th>
			<td><?= $this->Time->nice($translateString->modified) ?></td>
		</tr>
	</table>

	<h3>Translations</h3>
	<?php if ($translateString->translate_terms) { ?>
	<table class="table table-striped table-responsive">
		<tr>
			<th>Language</th>
			<th>Term</th>
		</tr>
		<?php foreach ($translateString->translate_terms as $translateTerm) { ?>
			<tr>
				<td>
					<?php //echo $this->Translation->flag($translateTerm->translate_language->iso2); ?> <?php echo h($translateTerm->translate_language->iso2); ?>
				</td>
				<td>
					<?php echo h($translateTerm->content); ?>
					<?php if ($translateTerm->content === '' && $translateTerm->translate_language->locale === Configure::read('Translate.defaultLocale')) { ?>
						<div class="defaulting" title="Default value"><?php echo h($translateString->name); ?></div>
					<?php } ?>
				</td>
			</tr>
		<?php } ?>
	</table>
	<?php } ?>

	<p><?php echo $this->Html->link('Translate', ['action' => 'translate', $translateString->id]); ?></p>

	<div class="row">
		<h3><?= __d('translate', 'References') ?></h3>
		<?= nl2br(h($translateString->references)); ?>
	</div>

	<div class="related">
		<h3><?= __d('translate', 'Related Translate Domains') ?></h3>
		<?php if (!empty($translateString->translate_domain)): ?>
		<table class="table table-horizontal">
			<tr>
			<th><?= __d('translate', 'Name') ?></th>
			<th><?= __d('translate', 'Active') ?></th>
			<th><?= __d('translate', 'Prio') ?></th>
			<th><?= __d('translate', 'Created') ?></th>
			<th><?= __d('translate', 'Modified') ?></th>
				<th class="actions"><?= __d('translate', 'Actions') ?></th>
			</tr>
			<tr>
				<td><?= h($translateString->translate_domain->name) ?></td>
				<td><?= $this->element('Translate.yes_no', ['value' => $translateString->translate_domain->active]); ?></td>
				<td><?= h($translateString->translate_domain->prio) ?></td>
				<td><?= h($translateString->translate_domain->created) ?></td>
				<td><?= h($translateString->translate_domain->modified) ?></td>
				<td class="actions">
					<?= $this->Html->link(__d('translate', 'View'), ['controller' => 'TranslateDomains', 'action' => 'view', $translateString->translate_domain->id]) ?>

					<?= $this->Html->link(__d('translate', 'Edit'), ['controller' => 'TranslateDomains', 'action' => 'edit', $translateString->translate_domain->id]) ?>

					<?= $this->Form->postLink(__d('translate', 'Delete'), ['controller' => 'TranslateDomains', 'action' => 'delete', $translateString->translate_domain->id], ['confirm' => __d('translate', 'Are you sure you want to delete # {0}?', $translateString->translate_domain->id)]) ?>

				</td>
			</tr>
		</table>
	<?php endif; ?>
	</div>
</div>
