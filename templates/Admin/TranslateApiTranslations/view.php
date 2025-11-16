<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateApiTranslation $translateApiTranslation
 */
?>
<div class="row">
	<div class="col-md-3">
		<div class="card">
			<div class="card-header">
				<h3 class="card-title"><i class="fa fa-cog"></i> <?= __d('translate', 'Actions') ?></h3>
			</div>
			<div class="card-body p-0">
				<ul class="list-group list-group-flush">
					<li class="list-group-item">
						<?= $this->Html->link('<i class="fa fa-edit"></i> ' . __d('translate', 'Edit API Translation'), ['action' => 'edit', $translateApiTranslation->id], ['escape' => false, 'class' => '']) ?>
					</li>
					<li class="list-group-item">
						<?= $this->Form->postLink('<i class="fa fa-trash"></i> ' . __d('translate', 'Delete API Translation'), ['action' => 'delete', $translateApiTranslation->id], ['escape' => false, 'class' => 'text-danger', 'confirm' => __d('translate', 'Are you sure you want to delete # {0}?', $translateApiTranslation->id)]) ?>
					</li>
					<li class="list-group-item">
						<?= $this->Html->link('<i class="fa fa-list"></i> ' . __d('translate', 'List API Translations'), ['action' => 'index'], ['escape' => false, 'class' => '']) ?>
					</li>
					<li class="list-group-item">
						<?= $this->Html->link('<i class="fa fa-plus"></i> ' . __d('translate', 'New API Translation'), ['action' => 'add'], ['escape' => false, 'class' => '']) ?>
					</li>
				</ul>
			</div>
		</div>
	</div>

	<div class="col-md-9">
		<div class="card">
			<div class="card-header">
				<h3 class="card-title"><i class="fa fa-cloud"></i> <?= h($translateApiTranslation->id) ?></h3>
			</div>
			<div class="card-body">
				<table class="table table-bordered">
					<tr>
						<th class="w-25"><?= __d('translate', 'From') ?></th>
						<td><?= h($translateApiTranslation->from) ?></td>
					</tr>
					<tr>
						<th><?= __d('translate', 'To') ?></th>
						<td><?= h($translateApiTranslation->to) ?></td>
					</tr>
					<tr>
						<th><?= __d('translate', 'Engine') ?></th>
						<td><?= h($translateApiTranslation->engine) ?></td>
					</tr>
					<tr>
						<th><?= __d('translate', 'Created') ?></th>
						<td><?= $this->Time->nice($translateApiTranslation->created) ?></td>
					</tr>
				</table>

				<div class="mt-4">
					<h4><?= __d('translate', 'Key') ?></h4>
					<div class="p-3 bg-light border rounded">
						<?= $this->Text->autoParagraph(h($translateApiTranslation->key)) ?>
					</div>
				</div>

				<div class="mt-4">
					<h4><?= __d('translate', 'Value') ?></h4>
					<div class="p-3 bg-light border rounded">
						<?= $this->Text->autoParagraph(h($translateApiTranslation->value)) ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
