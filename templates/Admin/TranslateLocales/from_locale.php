<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateLocale $translateLocale
 * @var mixed $existingLanguages
 * @var mixed $folders
 * @var mixed $path
 */
?>
<div class="row">
	<aside class="col-md-3">
		<div class="card">
			<div class="card-header">
				<h3 class="card-title"><i class="fa fa-bars"></i> <?= __d('translate', 'Actions') ?></h3>
			</div>
			<div class="list-group list-group-flush">
				<?= $this->Html->link(__d('translate', 'Overview'), ['controller' => 'Translate', 'action' => 'index'], ['class' => 'list-group-item list-group-item-action']) ?>
				<?= $this->Html->link(__d('translate', 'List Locales'), ['action' => 'index'], ['class' => 'list-group-item list-group-item-action']) ?>
			</div>
		</div>
	</aside>
	<div class="col-md-9">
		<div class="card">
			<div class="card-header">
				<h3 class="card-title"><i class="fa fa-download"></i> <?= __d('translate', 'Import Locales') ?></h3>
			</div>
			<div class="card-body">
				<p>
					Looking into <code><?= h($path) ?></code>
				</p>

				<?= $this->Form->create(null) ?>
					<fieldset>
						<legend><?= __dn('translate', '{0} locale found', '{0} locales found', count($folders), count($folders)) ?></legend>

					<ul class="list-unstyled">
					<?php
					foreach ($folders as $key => $folder) {
						if (array_key_exists($folder, $existingLanguages)) {
							echo '<li>' . $folder . ' (already exists)</li>';
						} else {
							echo '<li>';
							echo $this->Form->control('language.' . $folder . '.confirm', ['type' => 'checkbox', 'label' => $folder]);
							echo $this->Form->control('language.' . $folder . '.name', ['label' => __d('translate', 'Language name')]);
							echo '</li>';
						}
					}
					?>
					</ul>
					</fieldset>
			</div>
			<div class="card-footer">
				<?= $this->Form->submit(__d('translate', 'Submit'), ['class' => 'btn btn-primary']) ?>
				<?= $this->Form->end() ?>
			</div>
		</div>
	</div>
</div>
