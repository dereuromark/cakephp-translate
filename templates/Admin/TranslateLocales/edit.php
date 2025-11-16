<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateLocale $translateLocale
 * @var mixed $Languages
 */
?>
<div class="row">
	<aside class="col-md-3">
		<div class="card">
			<div class="card-header">
				<h3 class="card-title"><i class="fa fa-bars"></i> <?= __d('translate', 'Actions') ?></h3>
			</div>
			<div class="list-group list-group-flush">
				<?= $this->Form->postLink(
					__d('translate', 'Delete'),
					['action' => 'delete', $translateLocale->id],
					['confirm' => __d('translate', 'Are you sure you want to delete # {0}?', $translateLocale->id), 'class' => 'list-group-item list-group-item-action text-danger'],
				)
							?>
				<?= $this->Html->link(__d('translate', 'List Locales'), ['action' => 'index'], ['class' => 'list-group-item list-group-item-action']) ?>
			</div>
		</div>
	</aside>
	<div class="col-md-9">
		<div class="card">
			<div class="card-header">
				<h3 class="card-title"><i class="fa fa-edit"></i> <?= __d('translate', 'Edit Locale') ?></h3>
			</div>
			<?= $this->Form->create($translateLocale) ?>
			<div class="card-body">
				<fieldset>
					<?php
					echo $this->Form->control('name');
					echo $this->Form->control('locale');

					if (!empty($Languages)) {
						echo $this->Form->control('language_id', ['empty' => true]);
					} else {
						echo $this->Form->control('iso2');
					}

					echo $this->Form->control('active');
					?>
				</fieldset>
			</div>
			<div class="card-footer">
				<?= $this->Form->button(__d('translate', 'Submit'), ['class' => 'btn btn-primary']) ?>
			</div>
			<?= $this->Form->end() ?>
		</div>
	</div>
</div>
