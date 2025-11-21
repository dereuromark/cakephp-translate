<?php
/**
 * @var \App\View\AppView $this
 * @var string $appPath
 * @var string $localePath
 * @var array<string> $defaultPaths
 * @var string|null $output
 * @var string|null $command
 * @var int|null $returnCode
 * @var bool $isPlugin
 * @var string|null $pluginDomain
 * @var array<string, array{count: int, content: string}>|null $dryRunResults
 */

$this->assign('title', __d('translate', 'Run i18n Extract (Experimental)'));
?>

<nav class="actions col-sm-4 col-12">
	<ul class="nav nav-stacked">
		<li class="nav-item">
			<?= $this->Html->link(__d('translate', 'Back'), ['action' => 'index'], ['class' => 'nav-link']) ?>
		</li>
		<li class="nav-item">
			<?= $this->Html->link(__d('translate', 'Extract/Import'), ['action' => 'extract'], ['class' => 'nav-link']) ?>
		</li>
		<li class="nav-item">
			<?= $this->Html->link(__d('translate', 'Analyze'), ['action' => 'analyze'], ['class' => 'nav-link']) ?>
		</li>
	</ul>
</nav>

<div class="content col-sm-8 col-12">
	<h2>
		<i class="fas fa-flask"></i>
		<?= __d('translate', 'Run i18n Extract') ?>
		<span class="badge badge-warning"><?= __d('translate', 'Experimental') ?></span>
	</h2>

	<div class="alert alert-warning">
		<i class="fas fa-exclamation-triangle"></i>
		<strong><?= __d('translate', 'Warning:') ?></strong>
		<?= __d('translate', 'This runs the CakePHP i18n extract command. It will scan your source files and generate/update POT files. Make sure you have a backup of your locale files.') ?>
	</div>

	<?php if ($pluginDomain) { ?>
		<div class="alert alert-info">
			<i class="fas fa-info-circle"></i>
			<?= __d('translate', 'Plugin domain: <strong>{0}.pot</strong> (only this file will be kept, others like default.pot will be removed)', h($pluginDomain)) ?>
		</div>
	<?php } ?>

	<div class="card mb-3">
		<div class="card-header">
			<strong><?= __d('translate', 'Project Paths') ?></strong>
		</div>
		<div class="card-body">
			<dl class="row mb-0">
				<dt class="col-sm-3"><?= __d('translate', 'App Path') ?></dt>
				<dd class="col-sm-9"><code><?= h($appPath) ?></code></dd>

				<dt class="col-sm-3"><?= __d('translate', 'Locale Path') ?></dt>
				<dd class="col-sm-9"><code><?= h($localePath) ?></code></dd>
			</dl>
		</div>
	</div>

	<?= $this->Form->create(null) ?>
	<fieldset>
		<legend><?= __d('translate', 'Extraction Options') ?></legend>

		<?= $this->Form->control('paths', [
			'type' => 'textarea',
			'label' => __d('translate', 'Paths to scan (one per line)'),
			'rows' => 4,
			'default' => implode("\n", $defaultPaths),
			'help' => __d('translate', 'Leave empty to use defaults: src/ and templates/'),
		]) ?>

		<?= $this->Form->control('output_path', [
			'type' => 'text',
			'label' => __d('translate', 'Output Path'),
			'default' => $localePath,
			'help' => __d('translate', 'Where to write the POT files'),
		]) ?>

		<div class="row">
			<div class="col-md-4">
				<?= $this->Form->control('dry_run', [
					'type' => 'checkbox',
					'label' => __d('translate', 'Dry run (preview only)'),
					'default' => true,
				]) ?>
			</div>
			<div class="col-md-4">
				<?= $this->Form->control('merge', [
					'type' => 'checkbox',
					'label' => __d('translate', 'Merge with existing'),
					'default' => true,
				]) ?>
			</div>
			<div class="col-md-4">
				<?= $this->Form->control('overwrite', [
					'type' => 'checkbox',
					'label' => __d('translate', 'Overwrite existing'),
					'default' => true,
				]) ?>
			</div>
		</div>
		<?php if (!$isPlugin) { ?>
			<div class="row mt-2">
				<div class="col-md-4">
					<?= $this->Form->control('extract_core', [
						'type' => 'checkbox',
						'label' => __d('translate', 'Extract core strings'),
						'checked' => false,
					]) ?>
				</div>
			</div>
		<?php } ?>

		<div class="row mt-3">
			<div class="col-md-6">
				<?= $this->Form->control('direct_import', [
					'type' => 'checkbox',
					'label' => __d('translate', 'Import directly to database (skip POT file step)'),
					'default' => false,
				]) ?>
				<small class="text-muted"><?= __d('translate', 'When checked, the extracted strings will be imported directly into the database after extraction.') ?></small>
			</div>
		</div>
	</fieldset>

	<?= $this->Form->button(__d('translate', 'Run Extract'), [
		'class' => 'btn btn-warning',
	]) ?>
	<?= $this->Form->end() ?>

	<?php if ($command !== null) { ?>
		<hr class="my-4">

		<h3><?= __d('translate', 'Results') ?></h3>

		<div class="card mb-3">
			<div class="card-header">
				<strong><?= __d('translate', 'Command Executed') ?></strong>
			</div>
			<div class="card-body">
				<code class="text-break"><?= h($command) ?></code>
			</div>
		</div>

		<?php if ($returnCode !== null) { ?>
			<div class="card mb-3 <?= $returnCode === 0 ? 'border-success' : 'border-warning' ?>">
				<div class="card-header <?= $returnCode === 0 ? 'bg-success text-white' : 'bg-warning' ?>">
					<strong><?= __d('translate', 'Output') ?></strong>
					<span class="float-end">
						<?= __d('translate', 'Exit code: {0}', $returnCode . ' ( ' . ($returnCode === 0 ? 'OK' : 'ERROR') . ')') ?>
					</span>
				</div>
				<div class="card-body">
					<pre class="mb-0" style="max-height: 400px; overflow-y: auto;"><?= h($output) ?></pre>
				</div>
			</div>
		<?php } ?>

		<?php if ($dryRunResults) { ?>
			<div class="card mb-3 border-info">
				<div class="card-header bg-info text-white">
					<strong><i class="fas fa-eye"></i> <?= __d('translate', 'Dry Run Preview') ?></strong>
				</div>
				<div class="card-body">
					<p><?= __d('translate', 'The following POT files would be generated:') ?></p>

					<?php foreach ($dryRunResults as $filename => $data) { ?>
						<div class="card mb-2">
							<div class="card-header d-flex justify-content-between align-items-center">
								<strong><?= h($filename) ?></strong>
								<span class="badge bg-primary"><?= __d('translate', '{0} strings', $data['count']) ?></span>
							</div>
							<div class="card-body p-0">
								<pre class="mb-0 p-3" style="max-height: 300px; overflow-y: auto; font-size: 0.8rem;"><?= h($data['content']) ?></pre>
							</div>
						</div>
					<?php } ?>

					<div class="alert alert-warning mt-3 mb-0">
						<i class="fas fa-info-circle"></i>
						<?= __d('translate', 'This was a dry run. Uncheck "Dry run" and submit again to actually write the files.') ?>
					</div>
				</div>
			</div>
		<?php } elseif ($returnCode === 0) { ?>
			<div class="alert alert-info">
				<i class="fas fa-info-circle"></i>
				<?= __d('translate', 'POT files have been generated. You can now import them via the {0} page.', $this->Html->link(__d('translate', 'Extract/Import'), ['action' => 'extract'])) ?>
			</div>
		<?php } ?>
	<?php } ?>
</div>
