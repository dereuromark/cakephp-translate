<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateLocale $translateLocale
 * @var mixed $existingFolders
 * @var mixed $languages
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
				<h3 class="card-title"><i class="fa fa-upload"></i> <?= __d('translate', 'Export Locales') ?></h3>
			</div>
			<div class="card-body">
				<p>
					Checking <code><?= h($path) ?></code>
				</p>

				<?= $this->Form->create(null) ?>
					<fieldset>
						<legend><?= __dn('translate', '{0} locale', '{0} locales', count($languages), count($languages)) ?></legend>
					<div>
					<?php
					foreach ($languages as $key => $language) {
						if (in_array($key, $existingFolders, true)) {
							echo '<p><b>' . $key . '</b>' . ' - ' . $language . ' (already exists)</p>';
						} else {
							echo $this->Form->control('locale.' . $key . '.confirm', ['type' => 'checkbox', 'label' => $key . ' (' . $language . ')']);
						}
					}
					?>
					</div>
					</fieldset>
			</div>
			<div class="card-footer">
				<?= $this->Form->submit(__d('translate', 'Submit'), ['class' => 'btn btn-primary']) ?>
				<?= $this->Form->end() ?>
			</div>
		</div>
	</div>
</div>
