<?php
/**
 * @var \App\View\AppView $this
 * @var mixed $map
 * @var string $path
 */
?>
<div class="row">
	<aside class="col-md-3 col-sm-4 col-12">
		<div class="card">
			<div class="card-header">
				<h3 class="card-title"><i class="fa-solid fa-bars"></i> <?= __d('translate', 'Actions') ?></h3>
			</div>
			<div class="list-group list-group-flush">
				<?= $this->Html->link(__d('translate', 'List Translate Strings'), ['action' => 'index'], ['class' => 'list-group-item list-group-item-action']) ?>
			</div>
		</div>
	</aside>

	<div class="col-md-9 col-sm-8 col-12">
		<div class="card">
			<div class="card-header">
				<h3 class="card-title"><i class="fa-solid fa-file-export"></i> <?= __d('translate', 'Dump Translations') ?></h3>
			</div>
			<div class="card-body">
				<div class="alert alert-info">
					<i class="fa-solid fa-info-circle"></i>
					<strong><?= __d('translate', 'Files are stored in:') ?></strong>
					<code>...<?= h(str_replace(ROOT, '', $path)) ?><b>{locale}</b><?= '/' ?><b>{domain}</b>.po</code>
				</div>

				<?= $this->Form->create(null) ?>
					<fieldset>
						<legend><?= __d('translate', 'Languages and Domains') ?></legend>

						<?php if (empty($map)) { ?>
							<div class="alert alert-warning">
								<i class="fa-solid fa-exclamation-triangle"></i>
								<?= __d('translate', 'No active domains found. Please activate them if they already exist.') ?>
							</div>
						<?php } else { ?>
							<?= $this->Form->control('domains', [
								'multiple' => 'checkbox',
								'label' => __d('translate', 'Selection'),
								'options' => $map,
							]) ?>
						<?php } ?>
					</fieldset>

					<?php if (!empty($map)) { ?>
						<div class="mt-3">
							<?= $this->Form->submit(__d('translate', 'Submit'), ['class' => 'btn btn-primary']) ?>
						</div>
					<?php } ?>
				<?= $this->Form->end() ?>
			</div>
		</div>
	</div>
</div>
