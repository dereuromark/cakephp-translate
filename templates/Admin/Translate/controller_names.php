<?php
/**
 * @var \App\View\AppView $this
 * @var array<string, array<string, array{singular: string, plural: string}>> $controllerNames
 * @var string|null $projectPath
 */

$totalCount = 0;
foreach ($controllerNames as $names) {
	$totalCount += count($names);
}
?>

<div class="row">
	<aside class="col-md-3 col-sm-4 col-12">
		<div class="card">
			<div class="card-header">
				<h3 class="card-title"><i class="fa-solid fa-bars"></i> <?= __d('translate', 'Actions') ?></h3>
			</div>
			<div class="list-group list-group-flush">
				<?= $this->Html->link(__d('translate', 'Overview'), ['action' => 'index'], ['class' => 'list-group-item list-group-item-action']) ?>
				<?= $this->Html->link(__d('translate', 'List Strings'), ['controller' => 'TranslateStrings', 'action' => 'index'], ['class' => 'list-group-item list-group-item-action']) ?>
				<?= $this->Html->link(__d('translate', 'Add String'), ['controller' => 'TranslateStrings', 'action' => 'add'], ['class' => 'list-group-item list-group-item-action']) ?>
			</div>
		</div>

		<div class="card mt-3">
			<div class="card-header bg-info text-white">
				<h3 class="card-title mb-0"><i class="fa-solid fa-info-circle"></i> <?= __d('translate', 'Summary') ?></h3>
			</div>
			<div class="card-body">
				<p class="mb-2">
					<strong><?= __d('translate', 'Total Controllers') ?>:</strong>
					<span class="badge bg-primary"><?= $totalCount ?></span>
				</p>
				<p class="mb-2">
					<strong><?= __d('translate', 'Sources') ?>:</strong>
					<span class="badge bg-secondary"><?= count($controllerNames) ?></span>
				</p>
				<?php if ($projectPath) { ?>
				<p class="mb-0 small text-muted">
					<strong><?= __d('translate', 'Project Path') ?>:</strong><br>
					<code><?= h($projectPath) ?></code>
				</p>
				<?php } else { ?>
				<p class="mb-0 small text-muted">
					<?= __d('translate', 'Scanning loaded plugins (no project path configured).') ?>
				</p>
				<?php } ?>
			</div>
		</div>

		<div class="card mt-3">
			<div class="card-header">
				<h3 class="card-title mb-0"><i class="fa-solid fa-lightbulb"></i> <?= __d('translate', 'Usage') ?></h3>
			</div>
			<div class="card-body small">
				<p><?= __d('translate', 'Use these singular and plural forms as translation strings for your controller names.') ?></p>
				<p class="mb-0"><?= __d('translate', 'Add them to your PO files or import them via the Strings page.') ?></p>
			</div>
		</div>
	</aside>

	<div class="col-md-9 col-sm-8 col-12">
		<div class="card">
			<div class="card-header">
				<h3 class="card-title"><i class="fa-solid fa-cubes"></i> <?= __d('translate', 'Controller Names') ?></h3>
			</div>
			<div class="card-body p-0">
				<?php if (empty($controllerNames)) { ?>
				<div class="alert alert-warning m-3">
					<i class="fa-solid fa-exclamation-triangle"></i>
					<?= __d('translate', 'No controllers found. Make sure the project path is configured correctly.') ?>
				</div>
				<?php } else { ?>
					<?php foreach ($controllerNames as $source => $names) { ?>
				<div class="border-bottom">
					<h5 class="bg-light p-3 mb-0">
						<i class="fa-solid fa-folder-open"></i>
						<?= h($source) ?>
						<small class="text-muted">(<?= count($names) ?> <?= __d('translate', 'controllers') ?>)</small>
					</h5>
					<div class="table-responsive">
						<table class="table table-striped table-hover mb-0">
							<thead class="table-dark">
								<tr>
									<th><?= __d('translate', 'Controller') ?></th>
									<th><?= __d('translate', 'Singular') ?></th>
									<th><?= __d('translate', 'Plural') ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($names as $controller => $forms) { ?>
								<tr>
									<td>
										<code><?= h($controller) ?>Controller</code>
									</td>
									<td>
										<span class="badge bg-primary"><?= h($forms['singular']) ?></span>
									</td>
									<td>
										<span class="badge bg-success"><?= h($forms['plural']) ?></span>
									</td>
								</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
					<?php } ?>
				<?php } ?>
			</div>
		</div>
	</div>
</div>
