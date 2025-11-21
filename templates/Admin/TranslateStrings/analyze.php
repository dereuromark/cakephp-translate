<?php
/**
 * @var \App\View\AppView $this
 * @var array{issues: array<string, array<string, mixed>>, stats: array<string, int>, suggestions: array<string>}|null $result
 * @var string $content
 * @var array<string, string> $availableFiles
 * @var string|null $selectedFile
 */

use Cake\Core\Configure;

$this->assign('title', __d('translate', 'Analyze PO File'));
$isPreselected = $this->request->getQuery('file') !== null;
?>

<nav class="actions col-sm-4 col-12">
	<ul class="nav nav-stacked">
		<li class="nav-item">
			<?php echo $this->Html->link(__d('translate', 'Back'), ['action' => 'index'], ['class' => 'nav-link']); ?>
		</li>
		<li class="nav-item">
			<?php echo $this->Html->link(__d('translate', 'Extract/Import'), ['action' => 'extract'], ['class' => 'nav-link']); ?>
		</li>
	</ul>

	<?php if ($availableFiles) { ?>
		<h5 class="mt-4"><?= __d('translate', 'Quick Analyze') ?></h5>
		<div class="d-flex flex-wrap gap-1">
			<?php foreach ($availableFiles as $key => $label) { ?>
				<?= $this->Html->link(
					$label,
					['action' => 'analyze', '?' => ['file' => $key]],
					['class' => 'btn btn-sm ' . ($selectedFile === $key ? 'btn-primary' : 'btn-outline-secondary')],
				) ?>
			<?php } ?>
		</div>
	<?php } ?>
</nav>

<div class="content col-sm-8 col-12">
	<h2><?= __d('translate', 'PO File Analyzer') ?></h2>

	<?php if ($isPreselected && $selectedFile) { ?>
		<div class="alert alert-info">
			<i class="fas fa-file-alt"></i>
			<?= __d('translate', 'Analyzing: {0}', '<strong>' . h($availableFiles[$selectedFile] ?? $selectedFile) . '</strong>') ?>
		</div>
	<?php } else { ?>
		<p class="text-muted"><?= __d('translate', 'Select a file from the sidebar, or paste/upload content below.') ?></p>

		<?php echo $this->Form->create(null, ['type' => 'file']); ?>
		<fieldset>
			<legend><?= __d('translate', 'Input') ?></legend>

			<?php if ($availableFiles) { ?>
				<?php
				echo $this->Form->control('selected_file', [
					'type' => 'select',
					'label' => __d('translate', 'Select from Project'),
					'options' => $availableFiles,
					'empty' => '-- ' . __d('translate', 'Select a file') . ' --',
					'value' => $selectedFile,
				]);
				?>

				<div class="my-3 text-center text-muted">
					<strong><?= __d('translate', '- OR -') ?></strong>
				</div>
			<?php } ?>

			<?php
			echo $this->Form->control('file', [
				'type' => 'file',
				'label' => __d('translate', 'Upload PO/POT File'),
				'accept' => '.po,.pot',
			]);
			?>

			<div class="my-3 text-center text-muted">
				<strong><?= __d('translate', '- OR -') ?></strong>
			</div>

			<?php
			echo $this->Form->control('content', [
				'type' => 'textarea',
				'label' => __d('translate', 'Paste PO Content'),
				'rows' => 10,
				'value' => $isPreselected ? '' : $content,
				'placeholder' => 'msgid "Hello"
msgstr "Hallo"

msgid "{0} item"
msgid_plural "{0} items"
msgstr[0] "{0} Element"
msgstr[1] "{0} Elemente"',
			]);
			?>
		</fieldset>

		<?php echo $this->Form->button(__d('translate', 'Analyze'), ['class' => 'btn btn-primary']); ?>
		<?php echo $this->Form->end(); ?>
	<?php } ?>

	<?php if ($result !== null) { ?>
		<hr class="my-4">

		<h3><?= __d('translate', 'Analysis Results') ?></h3>

		<!-- Statistics -->
		<div class="card mb-3">
			<div class="card-header">
				<strong><?= __d('translate', 'Statistics') ?></strong>
			</div>
			<div class="card-body">
				<div class="row">
					<div class="col-md-4">
						<div class="small text-muted"><?= __d('translate', 'Total Strings') ?></div>
						<div class="h4"><?= $result['stats']['total'] ?></div>
					</div>
					<div class="col-md-4">
						<div class="small text-muted"><?= __d('translate', 'Translated') ?></div>
						<div class="h4 text-success"><?= $result['stats']['translated'] ?></div>
					</div>
					<div class="col-md-4">
						<div class="small text-muted"><?= __d('translate', 'Untranslated') ?></div>
						<div class="h4 text-warning"><?= $result['stats']['untranslated'] ?></div>
					</div>
				</div>
				<div class="row mt-3">
					<div class="col-md-4">
						<div class="small text-muted"><?= __d('translate', 'Fuzzy') ?></div>
						<div class="h5"><?= $result['stats']['fuzzy'] ?></div>
					</div>
					<div class="col-md-4">
						<div class="small text-muted"><?= __d('translate', 'Plurals') ?></div>
						<div class="h5"><?= $result['stats']['plurals'] ?></div>
					</div>
					<div class="col-md-4">
						<div class="small text-muted"><?= __d('translate', 'With Context') ?></div>
						<div class="h5"><?= $result['stats']['with_context'] ?></div>
					</div>
				</div>

				<?php if ($result['stats']['total'] > 0) { ?>
					<div class="progress mt-3" style="height: 25px;">
						<?php
						$translatedPct = round(($result['stats']['translated'] / $result['stats']['total']) * 100);
						$untranslatedPct = 100 - $translatedPct;
						?>
						<div class="progress-bar bg-success" style="width: <?= $translatedPct ?>%">
							<?= $translatedPct ?>% <?= __d('translate', 'translated') ?>
						</div>
						<div class="progress-bar bg-warning" style="width: <?= $untranslatedPct ?>%">
							<?= $untranslatedPct ?>%
						</div>
					</div>
				<?php } ?>
			</div>
		</div>

		<!-- Suggestions -->
		<?php if ($result['suggestions']) { ?>
			<div class="card mb-3 border-info">
				<div class="card-header bg-info text-white">
					<strong><?= __d('translate', 'Suggestions') ?></strong>
				</div>
				<div class="card-body">
					<ul class="mb-0">
						<?php foreach ($result['suggestions'] as $suggestion) { ?>
							<li><?= h($suggestion) ?></li>
						<?php } ?>
					</ul>
				</div>
			</div>
		<?php } ?>

		<!-- Issues -->
		<?php if ($result['issues']) { ?>
			<div class="card mb-3 border-danger">
				<div class="card-header bg-danger text-white">
					<strong><?= __d('translate', 'Issues Found') ?></strong>
					<span class="badge badge-light float-right"><?= count($result['issues']) ?></span>
				</div>
				<div class="card-body p-0">
					<table class="table table-striped table-sm mb-0">
						<thead>
							<tr>
								<th><?= __d('translate', 'String (msgid)') ?></th>
								<th><?= __d('translate', 'Issue') ?></th>
								<th><?= __d('translate', 'Details') ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($result['issues'] as $msgid => $issues) { ?>
								<?php foreach ($issues as $type => $details) { ?>
									<tr>
										<td><code><?= h($this->Text->truncate($msgid, 50)) ?></code></td>
										<td>
											<?php
											$badgeClass = match ($type) {
												'placeholder_mismatch', 'plural_placeholder_mismatch', 'mixed_placeholder_styles' => 'danger',
												'untranslated' => 'warning',
												'whitespace_mismatch', 'html_mismatch' => 'secondary',
												default => 'info',
											};
											?>
											<span class="badge badge-<?= $badgeClass ?>"><?= h(str_replace('_', ' ', $type)) ?></span>
										</td>
										<td class="small"><?= h($details['message'] ?? '') ?></td>
									</tr>
								<?php } ?>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</div>
		<?php } else { ?>
			<div class="alert alert-success">
				<strong><?= __d('translate', 'No issues found!') ?></strong>
				<?= __d('translate', 'Your PO file looks good.') ?>
			</div>
		<?php } ?>
	<?php } ?>
</div>
