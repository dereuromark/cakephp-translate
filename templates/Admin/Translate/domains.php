<?php
/**
 * @var \App\View\AppView $this
 * @var array<string, array{
 *     msgidCount: int,
 *     callCount: int,
 *     fileCount: int,
 *     sampleMsgids: array<string>,
 *     firstFiles: array<string>,
 *     coverage: array<string, string|null>,
 *     potFile: string|null,
 *     isDefaultDomain: bool,
 *     hasImportedStrings: bool,
 *     importedStringCount: int,
 *     stage: string,
 * }> $domainsReport
 * @var array<string> $paths
 * @var array<string> $availableLocales
 * @var array<string> $localePaths
 * @var string $normalizedDefault
 * @var string $projectPath
 */

/**
 * Shorten an absolute path to `APP/...` (relative to the project root).
 * Adds a trailing slash for directory paths.
 */
$shortenPath = function (string $abs) use ($projectPath): string {
	$root = rtrim($projectPath, DIRECTORY_SEPARATOR);
	$normalizedAbs = rtrim($abs, DIRECTORY_SEPARATOR);
	if ($normalizedAbs === $root) {
		return 'APP';
	}
	if (str_starts_with($normalizedAbs, $root . DIRECTORY_SEPARATOR)) {
		$shortened = 'APP/' . substr($normalizedAbs, strlen($root) + 1);
	} else {
		$shortened = $normalizedAbs;
	}
	if (is_dir($abs)) {
		$shortened .= '/';
	}

	return $shortened;
};

?>
<div class="row">
	<!-- Sidebar -->
	<nav class="col-lg-3 col-md-4 mb-4">
		<div class="card">
			<div class="card-header">
				<i class="fas fa-bars"></i> <?= __d('translate', 'Actions') ?>
			</div>
			<div class="list-group list-group-flush">
				<?= $this->Html->link(
					'<i class="fas fa-home"></i> ' . __d('translate', 'Overview'),
					['controller' => 'Translate', 'action' => 'index'],
					['escapeTitle' => false, 'class' => 'list-group-item list-group-item-action'],
				) ?>
				<?= $this->Html->link(
					'<i class="fas fa-folder"></i> ' . __d('translate', 'Translate Domains'),
					['controller' => 'TranslateDomains', 'action' => 'index'],
					['escapeTitle' => false, 'class' => 'list-group-item list-group-item-action'],
				) ?>
				<?= $this->Html->link(
					'<i class="fas fa-flask"></i> ' . __d('translate', 'Run i18n Extract'),
					['controller' => 'TranslateStrings', 'action' => 'extract'],
					['escapeTitle' => false, 'class' => 'list-group-item list-group-item-action'],
				) ?>
			</div>
		</div>

		<div class="card mt-3">
			<div class="card-header">
				<i class="fas fa-folder-open"></i> <?= __d('translate', 'Scanned Paths') ?>
			</div>
			<ul class="list-group list-group-flush small">
				<?php foreach ($paths as $p) { ?>
					<li class="list-group-item" title="<?= h($p) ?>"><code><?= h($shortenPath($p)) ?></code></li>
				<?php } ?>
			</ul>
		</div>

		<div class="card mt-3">
			<div class="card-header">
				<i class="fas fa-info-circle"></i> <?= __d('translate', 'Locale Paths') ?>
			</div>
			<ul class="list-group list-group-flush small">
				<?php foreach ($localePaths as $lp) { ?>
					<li class="list-group-item" title="<?= h($lp) ?>"><code><?= h($shortenPath($lp)) ?></code></li>
				<?php } ?>
			</ul>
		</div>
	</nav>

	<!-- Main Content -->
	<div class="col-lg-9 col-md-8">
		<div class="card">
			<div class="card-header d-flex align-items-center justify-content-between">
				<h2 class="mb-0">
					<i class="fas fa-search"></i>
					<?= __d('translate', 'Detected Domains') ?>
				</h2>
				<span class="badge bg-secondary">
					<?= count($domainsReport) ?> <?= __d('translate', 'domains') ?>
				</span>
			</div>
			<div class="card-body">
				<p class="text-muted">
					<?= __d('translate', 'Scanned the project source for `__d(domain, ...)` calls. Each row shows whether an app-level PO file exists for that domain in each available locale.') ?>
				</p>

				<?php if (!$domainsReport) { ?>
					<div class="alert alert-info">
						<?= __d('translate', 'No `__d()` calls found in the scanned paths.') ?>
					</div>
				<?php } else { ?>
					<div class="table-responsive">
						<table class="table table-hover align-middle">
							<thead>
								<tr>
									<th><?= __d('translate', 'Domain') ?></th>
									<th class="text-end"><?= __d('translate', 'msgids') ?></th>
									<th class="text-end"><?= __d('translate', 'Calls') ?></th>
									<th class="text-end"><?= __d('translate', 'Files') ?></th>
									<th><?= __d('translate', 'POT') ?></th>
									<?php foreach ($availableLocales as $locale) { ?>
										<th class="text-center" style="font-family:monospace"><?= h($locale) ?></th>
									<?php } ?>
									<th><?= __d('translate', 'Suggestion') ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($domainsReport as $domain => $info) { ?>
									<tr>
										<td>
											<strong><?= h($domain) ?></strong>
											<?php if ($info['isDefaultDomain']) { ?>
												<span class="badge bg-secondary ms-1" title="<?= __d('translate', 'CakePHP default domain — typically translated via the app default.po') ?>">default</span>
											<?php } ?>
										</td>
										<td class="text-end"><?= $info['msgidCount'] ?></td>
										<td class="text-end"><?= $info['callCount'] ?></td>
										<td class="text-end"><?= $info['fileCount'] ?></td>
										<td>
											<?php if ($info['potFile']) { ?>
												<i class="fas fa-check text-success" title="<?= h($info['potFile']) ?>"></i>
											<?php } else { ?>
												<i class="fas fa-times text-muted" title="<?= __d('translate', 'No app-level POT file') ?>"></i>
											<?php } ?>
										</td>
										<?php foreach ($availableLocales as $locale) {
											$po = $info['coverage'][$locale] ?? null;
										?>
											<td class="text-center">
												<?php if ($po) { ?>
													<i class="fas fa-check text-success" title="<?= h($po) ?>"></i>
												<?php } else { ?>
													<i class="fas fa-times text-danger" title="<?= __d('translate', 'Missing app-level {0}.po — falls back to default domain', $domain) ?>"></i>
												<?php } ?>
											</td>
										<?php } ?>
										<td>
											<?php switch ($info['stage']) {
												case 'default': ?>
													<span class="text-muted small"><?= __d('translate', 'Use the regular i18n extract for the default domain.') ?></span>
													<?php break;
												case 'extract': ?>
													<span class="badge bg-secondary"><?= __d('translate', 'Stage 1: Extract') ?></span>
													<div class="small text-warning mt-1">
														<?= __d('translate', 'No POT yet — run i18n extract to generate it.') ?>
													</div>
													<div class="mt-1">
														<?= $this->Html->link(
															'<i class="fas fa-flask"></i> ' . __d('translate', 'Run i18n Extract'),
															['controller' => 'TranslateStrings', 'action' => 'runExtract'],
															['escapeTitle' => false, 'class' => 'btn btn-sm btn-outline-primary'],
														) ?>
													</div>
													<?php break;
												case 'import': ?>
													<span class="badge bg-info"><?= __d('translate', 'Stage 2: Import') ?></span>
													<div class="small text-warning mt-1">
														<?= __d('translate', 'POT exists but no strings imported — load it into the DB.') ?>
													</div>
													<div class="mt-1">
														<?= $this->Html->link(
															'<i class="fas fa-file-import"></i> ' . __d('translate', 'Import {0}.pot', $domain),
															['controller' => 'TranslateStrings', 'action' => 'extract', '?' => ['domain' => $domain]],
															['escapeTitle' => false, 'class' => 'btn btn-sm btn-outline-info'],
														) ?>
													</div>
													<?php break;
												case 'dump': ?>
													<span class="badge bg-warning text-dark"><?= __d('translate', 'Stage 3: Dump') ?></span>
													<div class="small text-warning mt-1">
														<?= __d('translate', '{0} string(s) in DB but no app-level {1}.po — dump to filesystem so the app can load it.', $info['importedStringCount'], $domain) ?>
													</div>
													<div class="mt-1">
														<?= $this->Html->link(
															'<i class="fas fa-file-export"></i> ' . __d('translate', 'Dump translations'),
															['controller' => 'TranslateStrings', 'action' => 'dump'],
															['escapeTitle' => false, 'class' => 'btn btn-sm btn-outline-warning'],
														) ?>
													</div>
													<?php break;
												case 'covered':
												default: ?>
													<span class="badge bg-success"><?= __d('translate', 'Covered') ?></span>
													<div class="small text-muted mt-1">
														<?php if ($info['hasImportedStrings']) { ?>
															<?= __d('translate', '{0} string(s) in DB, default-locale .po present.', $info['importedStringCount']) ?>
														<?php } else { ?>
															<?= __d('translate', 'Default-locale .po present (managed outside the DB workflow).') ?>
														<?php } ?>
													</div>
													<div class="mt-1">
														<?= $this->Html->link(
															'<i class="fas fa-flask"></i> ' . __d('translate', 'Re-extract'),
															['controller' => 'TranslateStrings', 'action' => 'runExtract'],
															['escapeTitle' => false, 'class' => 'btn btn-sm btn-outline-secondary'],
														) ?>
													</div>
													<?php break;
											} ?>
										</td>
									</tr>
									<tr class="table-light">
										<td colspan="<?= 6 + count($availableLocales) ?>" class="small text-muted">
											<?php if ($info['firstFiles']) { ?>
												<strong><?= __d('translate', 'Used in') ?>:</strong>
												<?php foreach ($info['firstFiles'] as $i => $f) { ?>
													<?= $i > 0 ? ', ' : '' ?><code><?= h($shortenPath($f)) ?></code>
												<?php } ?>
												<?php if ($info['fileCount'] > count($info['firstFiles'])) { ?>
													<em>(+<?= $info['fileCount'] - count($info['firstFiles']) ?> <?= __d('translate', 'more') ?>)</em>
												<?php } ?>
												<br>
											<?php } ?>
											<?php if ($info['sampleMsgids']) { ?>
												<strong><?= __d('translate', 'Sample msgids') ?>:</strong>
												<?php foreach ($info['sampleMsgids'] as $i => $m) { ?>
													<?= $i > 0 ? ' · ' : '' ?><code><?= h(mb_strimwidth((string)$m, 0, 60, '…')) ?></code>
												<?php } ?>
											<?php } ?>
										</td>
									</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				<?php } ?>
			</div>
		</div>
	</div>
</div>
