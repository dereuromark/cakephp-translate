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

			<?php
			echo $this->Form->control('key_based', [
				'type' => 'select',
				'label' => __d('translate', 'Translation Style'),
				'options' => [
					'' => __d('translate', 'Auto-detect'),
					'0' => __d('translate', 'Text-based (msgid is readable text)'),
					'1' => __d('translate', 'Key-based (msgid is a key like "user.profile.title")'),
				],
				'default' => '',
			]);
			?>
			<div class="alert alert-secondary small mt-2">
				<i class="fas fa-info-circle"></i>
				<strong><?= __d('translate', 'Auto-detect:') ?></strong>
				<?= __d('translate', 'If msgid has no spaces and matches key patterns (foo.bar.baz, foo_bar_baz, fooBarBaz), HTML/whitespace checks are skipped.') ?>
				<br>
				<strong><?= __d('translate', 'Key-based:') ?></strong>
				<?= __d('translate', 'Use this when msgid is a translation key, not actual text. Skips HTML/whitespace mismatch checks.') ?>
			</div>
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
					<span class="badge bg-light text-dark float-end"><?= count($result['issues']) ?></span>
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
										<td>
											<code><?= h($msgid) ?></code>
											<?php if (!empty($details['msgid_plural'])) { ?>
												<br><small class="text-muted"><?= __d('translate', 'Plural:') ?> <code><?= h($details['msgid_plural']) ?></code></small>
											<?php } ?>
											<?php if (!empty($details['msgstr'])) { ?>
												<br><small class="text-muted"><?= __d('translate', 'Translation:') ?> <code><?= h($details['msgstr']) ?></code></small>
											<?php } ?>
										</td>
										<td>
											<?php
											$badgeClass = match ($type) {
												'placeholder_mismatch', 'plural_placeholder_mismatch', 'mixed_placeholder_styles' => 'danger',
												'whitespace_mismatch', 'html_mismatch' => 'secondary',
												'whitespace_warning' => 'warning',
												default => 'info',
											};
											?>
											<span class="badge bg-<?= $badgeClass ?>"><?= h(str_replace('_', ' ', $type)) ?></span>
										</td>
										<td class="small">
											<?= h($details['message'] ?? '') ?>
											<?php if (!empty($details['expected']) && !empty($details['actual'])) { ?>
												<br>
												<span class="text-success"><?= __d('translate', 'Expected:') ?> <?= h(json_encode($details['expected'])) ?></span>
												<br>
												<span class="text-danger"><?= __d('translate', 'Actual:') ?> <?= h(json_encode($details['actual'])) ?></span>
											<?php } ?>
											<?php if (!empty($details['fixed_msgid'])) { ?>
												<hr class="my-2">
												<strong><?= __d('translate', 'Suggested fix for msgid:') ?></strong>
												<br>
												<code class="text-success" id="fix-msgid-<?= md5($msgid . $type) ?>"><?= h($details['fixed_msgid']) ?></code>
												<button type="button" class="btn btn-sm btn-outline-success ms-2" onclick="copyFix(this, 'fix-msgid-<?= md5($msgid . $type) ?>')" title="<?= __d('translate', 'Copy to clipboard') ?>">
													<i class="fas fa-copy"></i> <?= __d('translate', 'Copy') ?>
												</button>
											<?php } ?>
											<?php if (!empty($details['fixed_msgstr'])) { ?>
												<hr class="my-2">
												<strong><?= __d('translate', 'Suggested fix for msgstr:') ?></strong>
												<br>
												<code class="text-success" id="fix-<?= md5($msgid . $type) ?>"><?= h($details['fixed_msgstr']) ?></code>
												<button type="button" class="btn btn-sm btn-outline-success ms-2" onclick="copyFix(this, 'fix-<?= md5($msgid . $type) ?>')" title="<?= __d('translate', 'Copy to clipboard') ?>">
													<i class="fas fa-copy"></i> <?= __d('translate', 'Copy') ?>
												</button>
											<?php } ?>
										</td>
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

<script>
function copyFix(btn, elementId) {
	var text = document.getElementById(elementId).textContent;
	if (navigator.clipboard) {
		navigator.clipboard.writeText(text).then(function() {
			btn.classList.remove('btn-outline-success');
			btn.classList.add('btn-success');
			btn.innerHTML = '<i class="fas fa-check"></i> <?= __d('translate', 'Copied!') ?>';
			setTimeout(function() {
				btn.classList.remove('btn-success');
				btn.classList.add('btn-outline-success');
				btn.innerHTML = '<i class="fas fa-copy"></i> <?= __d('translate', 'Copy') ?>';
			}, 2000);
		});
	} else {
		// Fallback for older browsers
		var textarea = document.createElement('textarea');
		textarea.value = text;
		document.body.appendChild(textarea);
		textarea.select();
		document.execCommand('copy');
		document.body.removeChild(textarea);
		btn.innerHTML = '<i class="fas fa-check"></i> <?= __d('translate', 'Copied!') ?>';
		setTimeout(function() {
			btn.innerHTML = '<i class="fas fa-copy"></i> <?= __d('translate', 'Copy') ?>';
		}, 2000);
	}
}
</script>
