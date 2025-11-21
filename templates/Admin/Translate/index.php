<?php
/**
 * @var \App\View\AppView $this
 * @var array $count
 * @var mixed $coverage
 * @var array $projectSwitchArray
 * @var \Translate\Model\Entity\TranslateProject|null $currentProject
 * @var array<\Translate\Model\Entity\TranslateString> $recentStrings
 * @var array<\Translate\Model\Entity\TranslateTerm> $recentTerms
 * @var array $auditLogs
 * @var array<\Translate\Model\Entity\TranslateLocale> $languages
 * @var array $localeStats
 * @var array $confirmationStats
 * @var array<\Translate\Model\Entity\TranslateString> $recentImports
 * @var array $auditData
 */

use Cake\Core\Configure;

$totalCoverage = (int)$this->Translation->totalCoverage($coverage);
$totalColor = $this->Translation->getColor($totalCoverage);
?>

<div class="row mb-2">
	<div class="col-12">
		<div class="card">
			<div class="card-body py-2">
				<div class="row align-items-center">
					<div class="col-md-4">
						<?php if (!empty($currentProject)) { ?>
							<strong><i class="fas fa-project-diagram text-primary"></i> <?= h($currentProject->name) ?></strong>
							<div class="btn-group ms-2" role="group">
								<?= $this->Html->link(
									'<i class="fas fa-edit"></i>',
									['controller' => 'TranslateProjects', 'action' => 'edit', $currentProject->id],
									['escape' => false, 'class' => 'btn btn-outline-primary btn-sm', 'title' => __d('translate', 'Edit Project')],
								) ?>
								<?= $this->Html->link(
									'<i class="fas fa-list"></i>',
									['controller' => 'TranslateProjects', 'action' => 'index'],
									['escape' => false, 'class' => 'btn btn-outline-secondary btn-sm', 'title' => __d('translate', 'All Projects')],
								) ?>
								<?= $this->Html->link(
									'<i class="fas fa-language"></i>',
									['controller' => 'TranslateStrings', 'action' => 'translate'],
									['escape' => false, 'class' => 'btn btn-success btn-sm', 'title' => __d('translate', 'Start Translating')],
								) ?>
							</div>
						<?php } else { ?>
							<span class="text-warning"><i class="fas fa-exclamation-triangle"></i> <?= __d('translate', 'No project selected') ?></span>
							<?= $this->Html->link(
								'<i class="fas fa-plus-circle"></i> ' . __d('translate', 'Create'),
								['controller' => 'TranslateProjects', 'action' => 'add'],
								['escape' => false, 'class' => 'btn btn-success btn-sm ms-2'],
							) ?>
						<?php } ?>
					</div>
					<?php if (is_array($count)) { ?>
					<div class="col-md-6">
						<div class="d-flex justify-content-around align-items-center">
							<div class="text-center">
								<i class="fas fa-globe text-warning"></i>
								<strong class="ms-1"><?= number_format($count['locales']) ?></strong>
								<small class="text-muted ms-1">
									<?= $this->Html->link(__d('translate', 'Locales'), ['controller' => 'TranslateLocales']) ?>
								</small>
							</div>
							<div class="text-center">
								<i class="fas fa-folder text-primary"></i>
								<strong class="ms-1"><?= number_format($count['domains']) ?></strong>
								<small class="text-muted ms-1">
									<?= $this->Html->link(__d('translate', 'Domains'), ['controller' => 'TranslateDomains']) ?>
								</small>
							</div>
							<div class="text-center">
								<i class="fas fa-file-alt text-info"></i>
								<strong class="ms-1"><?= number_format($count['strings']) ?></strong>
								<small class="text-muted ms-1">
									<?= $this->Html->link(__d('translate', 'Strings'), ['controller' => 'TranslateStrings']) ?>
								</small>
							</div>
							<div class="text-center">
								<i class="fas fa-comments text-success"></i>
								<strong class="ms-1"><?= number_format($count['translations']) ?></strong>
								<small class="text-muted ms-1">
									<?= $this->Html->link(__d('translate', 'Terms'), ['controller' => 'TranslateTerms']) ?>
								</small>
							</div>
						</div>
					</div>
					<?php } ?>
					<div class="col-md-2 text-end">
						<span class="badge bg-success" style="font-size: 1.5rem; padding: 0.5rem 1rem;">
							<i class="fas fa-chart-pie"></i> <?= $totalCoverage ?>%
						</span>
						<small class="text-muted d-block"><?= __d('translate', 'Overall Coverage') ?></small>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Coverage by Language & Translation Quality -->
<div class="row mb-2">
	<!-- Coverage by Language -->
	<?php if (is_array($count) && !empty($coverage)) { ?>
	<div class="col-md-6">
		<div class="card">
			<div class="card-header py-2">
				<h6 class="mb-0"><i class="fas fa-chart-bar"></i> <?= __d('translate', 'Coverage by Language') ?></h6>
			</div>
			<div class="card-body p-2">
				<?= $this->element('coverage_table', []) ?>
			</div>
		</div>
	</div>
	<?php } ?>

	<!-- Translation Quality -->
	<?php if (!empty($confirmationStats)) { ?>
	<div class="col-md-6">
		<div class="card">
			<div class="card-header bg-warning text-dark py-2 d-flex justify-content-between align-items-center">
				<h6 class="mb-0"><i class="fas fa-check-circle"></i> <?= __d('translate', 'Translation Quality') ?></h6>
				<?= $this->Html->link(
					'<i class="fas fa-tasks"></i> ' . __d('translate', 'Batch Confirm'),
					['controller' => 'TranslateTerms', 'action' => 'pending'],
					['escape' => false, 'class' => 'btn btn-sm btn-dark'],
				) ?>
			</div>
			<div class="card-body p-2">
				<table class="table table-sm table-hover mb-0">
					<thead>
						<tr>
							<th><?= __d('translate', 'Locale') ?></th>
							<th class="text-center"><?= __d('translate', 'Confirmed') ?></th>
							<th class="text-center"><?= __d('translate', 'Unconfirmed') ?></th>
							<th><?= __d('translate', 'Progress') ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($confirmationStats as $stats) { ?>
						<tr>
							<td>
								<?= $this->Translation->flag($this->Translation->resolveFlagCode($stats['locale'])) ?>
								<strong><?= h($stats['locale']->locale) ?></strong>
							</td>
							<td class="text-center">
								<span class="badge bg-success"><?= number_format($stats['confirmed']) ?></span>
							</td>
							<td class="text-center">
								<span class="badge bg-secondary"><?= number_format($stats['unconfirmed']) ?></span>
							</td>
							<td>
								<div class="progress" style="height: 20px;">
									<div class="progress-bar <?= $stats['percentage'] >= 80 ? 'bg-success' : ($stats['percentage'] >= 50 ? 'bg-warning' : 'bg-danger') ?>"
										role="progressbar"
										style="width: <?= $stats['percentage'] ?>%"
										aria-valuenow="<?= $stats['percentage'] ?>"
										aria-valuemin="0"
										aria-valuemax="100">
										<small><strong><?= $stats['percentage'] ?>%</strong></small>
									</div>
								</div>
							</td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
				<small class="text-muted d-block mt-2">
					<i class="fas fa-info-circle"></i>
					<?= __d('translate', 'Confirmed translations have been reviewed and approved for production use.') ?>
				</small>
			</div>
		</div>
	</div>
	<?php } ?>
</div>

<!-- Recent Activity -->
<div class="row mb-2">
	<!-- Recent Strings -->
	<?php if (!empty($recentStrings)) { ?>
	<div class="col-md-6">
		<div class="card">
			<div class="card-header bg-info text-white py-2">
				<h6 class="mb-0"><i class="fas fa-file-alt"></i> <?= __d('translate', 'Recent Strings') ?></h6>
			</div>
			<div class="card-body p-0">
				<div class="list-group list-group-flush">
					<?php foreach (array_slice($recentStrings, 0, 3) as $string) { ?>
					<div class="list-group-item py-2">
						<div class="d-flex justify-content-between align-items-center">
							<div class="flex-grow-1">
								<small><strong><?= h($string->name) ?></strong></small>
								<br>
								<small class="text-muted">
									<?= h($string->translate_domain->name) ?>
									<?php if ($string->modified) { ?>
										&middot; <?= $this->Time->timeAgoInWords($string->modified) ?>
									<?php } ?>
								</small>
							</div>
							<div class="btn-group" role="group">
								<button type="button" class="btn btn-sm btn-outline-info"
									data-bs-toggle="modal"
									data-bs-target="#stringModal<?= $string->id ?>"
									title="<?= __d('translate', 'View Details') ?>">
									<i class="fas fa-eye"></i>
								</button>
								<?= $this->Html->link(
									'<i class="fas fa-edit"></i>',
									['controller' => 'TranslateStrings', 'action' => 'translate', $string->id],
									['escape' => false, 'class' => 'btn btn-sm btn-outline-primary', 'title' => __d('translate', 'Translate')],
								) ?>
							</div>
						</div>
					</div>
					<!-- Modal for string details -->
					<div class="modal fade" id="stringModal<?= $string->id ?>" tabindex="-1">
						<div class="modal-dialog modal-lg">
							<div class="modal-content">
								<div class="modal-header">
									<h6 class="modal-title"><?= __d('translate', 'String Details') ?></h6>
									<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
								</div>
								<div class="modal-body">
									<table class="table table-sm">
										<tr>
											<th width="30%"><?= __d('translate', 'String') ?>:</th>
											<td><code><?= h($string->name) ?></code></td>
										</tr>
										<?php if ($string->plural) { ?>
										<tr>
											<th><?= __d('translate', 'Plural') ?>:</th>
											<td><code><?= h($string->plural) ?></code></td>
										</tr>
										<?php } ?>
										<?php if ($string->context) { ?>
										<tr>
											<th><?= __d('translate', 'Context') ?>:</th>
											<td><code><?= h($string->context) ?></code></td>
										</tr>
										<?php } ?>
										<tr>
											<th><?= __d('translate', 'Domain') ?>:</th>
											<td><?= h($string->translate_domain->name) ?></td>
										</tr>
										<tr>
											<th><?= __d('translate', 'Modified') ?>:</th>
											<td><?= $this->Time->format($string->modified, 'yyyy-MM-dd HH:mm:ss') ?></td>
										</tr>
										<tr>
											<th><?= __d('translate', 'Created') ?>:</th>
											<td><?= $this->Time->format($string->created, 'yyyy-MM-dd HH:mm:ss') ?></td>
										</tr>
										<?php if ($string->last_import) { ?>
										<tr>
											<th><?= __d('translate', 'Last Import') ?>:</th>
											<td><?= $this->Time->format($string->last_import, 'yyyy-MM-dd') ?></td>
										</tr>
										<?php } ?>
										<tr>
											<th><?= __d('translate', 'Flags') ?>:</th>
											<td>
												<?php if ($string->is_html) { ?>
													<span class="badge bg-warning">HTML</span>
												<?php } ?>
												<?php if ($string->plural) { ?>
													<span class="badge bg-info">Plural</span>
												<?php } ?>
												<?php if ($string->skipped) { ?>
													<span class="badge bg-secondary">Skipped</span>
												<?php } ?>
											</td>
										</tr>
									</table>
								</div>
								<div class="modal-footer">
									<?= $this->Html->link(
										__d('translate', 'Translate'),
										['controller' => 'TranslateStrings', 'action' => 'translate', $string->id],
										['class' => 'btn btn-primary btn-sm'],
									) ?>
									<?= $this->Html->link(
										__d('translate', 'View'),
										['controller' => 'TranslateStrings', 'action' => 'view', $string->id],
										['class' => 'btn btn-secondary btn-sm'],
									) ?>
								</div>
							</div>
						</div>
					</div>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
	<?php } ?>

	<!-- Recent Translations -->
	<?php if (!empty($recentTerms)) { ?>
	<div class="col-md-6">
		<div class="card">
			<div class="card-header bg-success text-white py-2">
				<h6 class="mb-0"><i class="fas fa-comments"></i> <?= __d('translate', 'Recent Translations') ?></h6>
			</div>
			<div class="card-body p-0">
				<div class="list-group list-group-flush">
					<?php foreach (array_slice($recentTerms, 0, 3) as $term) { ?>
					<div class="list-group-item py-2">
						<div class="d-flex justify-content-between align-items-center">
							<div class="flex-grow-1">
								<small>
									<span class="badge bg-primary"><?= h($term->translate_locale->locale) ?></span>
									<strong><?= h($term->translate_string->name) ?></strong>
								</small>
								<br>
								<small class="text-muted"><?= $this->Text->truncate(h($term->content), 60) ?></small>
							</div>
							<div class="btn-group" role="group">
								<button type="button" class="btn btn-sm btn-outline-info"
									data-bs-toggle="modal"
									data-bs-target="#termModal<?= $term->id ?>"
									title="<?= __d('translate', 'View Translation') ?>">
									<i class="fas fa-eye"></i>
								</button>
								<?= $this->Html->link(
									'<i class="fas fa-edit"></i>',
									['controller' => 'TranslateTerms', 'action' => 'edit', $term->id],
									['escape' => false, 'class' => 'btn btn-sm btn-outline-primary', 'title' => __d('translate', 'Edit')],
								) ?>
							</div>
						</div>
					</div>
					<!-- Modal for term details -->
					<div class="modal fade" id="termModal<?= $term->id ?>" tabindex="-1">
						<div class="modal-dialog modal-lg">
							<div class="modal-content">
								<div class="modal-header">
									<h6 class="modal-title">
										<?= $this->Translation->flag($this->Translation->resolveFlagCode($term->translate_locale)) ?>
										<?= h($term->translate_locale->locale) ?> - <?= __d('translate', 'Translation Change') ?>
									</h6>
									<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
								</div>
								<div class="modal-body">
									<?php
									// Try to get audit log data for this term to show unified diff
									$auditKey = 'TranslateTerms_' . $term->id;
									$hasAudit = !empty($auditData[$auditKey]);
									$oldData = null;
									if ($hasAudit && isset($auditData[$auditKey][0])) {
										// Get the most recent audit log (first one, as they're sorted DESC)
										$auditLog = $auditData[$auditKey][0];
										// Original data is in the audit log's 'original' field
										if (!empty($auditLog->original)) {
											$oldData = json_decode($auditLog->original, true);
										}
									}
									?>

									<?php if ($hasAudit && $oldData && isset($oldData['content']) && $oldData['content'] !== $term->content) { ?>
									<!-- Unified Diff Display -->
									<div class="alert alert-info mb-3">
										<i class="fas fa-code-branch"></i> <?= __d('translate', 'Changes from last edit') ?>
										<small class="float-end"><?= $this->Time->format($term->modified, 'yyyy-MM-dd HH:mm:ss') ?></small>
									</div>

									<div class="card mb-3">
										<div class="card-header py-1 bg-light">
											<small><strong><?= __d('translate', 'Unified Diff') ?></strong></small>
										</div>
										<div class="card-body p-0">
											<pre class="mb-0" style="background: #f8f9fa; padding: 10px; border: none; font-size: 12px; line-height: 1.4;"><code><?php
											// Generate unified diff style output
											$oldLines = explode("\n", $oldData['content']);
											$newLines = explode("\n", $term->content);

											// Show context header
											echo '<span style="color: #999;">@@ Translation @@</span>' . "\n";

											// Simple line-by-line diff
											$maxLines = max(count($oldLines), count($newLines));
											for ($i = 0; $i < $maxLines; $i++) {
												$oldLine = $oldLines[$i] ?? '';
												$newLine = $newLines[$i] ?? '';

												if ($oldLine !== $newLine) {
													if ($oldLine !== '') {
														echo '<span style="background: #ffdddd; color: #d00;">-' . h($oldLine) . '</span>' . "\n";
													}
													if ($newLine !== '') {
														echo '<span style="background: #ddffdd; color: #080;">+' . h($newLine) . '</span>' . "\n";
													}
												} else {
													echo ' ' . h($oldLine) . "\n";
												}
											}
											?></code></pre>
										</div>
									</div>
									<?php } ?>

									<h6><?= __d('translate', 'Details') ?>:</h6>
									<table class="table table-sm">
										<tr>
											<th width="30%"><?= __d('translate', 'Original String') ?>:</th>
											<td><code><?= h($term->translate_string->name) ?></code></td>
										</tr>
										<tr>
											<th><?= __d('translate', 'Current Translation') ?>:</th>
											<td><code class="text-success"><?= h($term->content) ?></code></td>
										</tr>
										<?php if ($term->plural_2) { ?>
										<tr>
											<th><?= __d('translate', 'Plural Translation') ?>:</th>
											<td><code class="text-success"><?= h($term->plural_2) ?></code></td>
										</tr>
										<?php } ?>
										<?php if ($term->comment) { ?>
										<tr>
											<th><?= __d('translate', 'Comment') ?>:</th>
											<td><?= h($term->comment) ?></td>
										</tr>
										<?php } ?>
										<tr>
											<th><?= __d('translate', 'Locale') ?>:</th>
											<td><?= h($term->translate_locale->name) ?> (<?= h($term->translate_locale->locale) ?>)</td>
										</tr>
										<tr>
											<th><?= __d('translate', 'Domain') ?>:</th>
											<td><?= h($term->translate_string->translate_domain->name) ?></td>
										</tr>
										<tr>
											<th><?= __d('translate', 'Modified') ?>:</th>
											<td><?= $this->Time->format($term->modified, 'yyyy-MM-dd HH:mm:ss') ?></td>
										</tr>
										<tr>
											<th><?= __d('translate', 'Status') ?>:</th>
											<td>
												<?php if ($term->confirmed) { ?>
													<span class="badge bg-success">
														<i class="fas fa-check-circle"></i> <?= __d('translate', 'Confirmed') ?>
													</span>
												<?php } else { ?>
													<span class="badge bg-secondary">
														<i class="fas fa-clock"></i> <?= __d('translate', 'Unconfirmed') ?>
													</span>
												<?php } ?>
											</td>
										</tr>
									</table>
								</div>
								<div class="modal-footer">
									<?= $this->Html->link(
										__d('translate', 'Edit'),
										['controller' => 'TranslateTerms', 'action' => 'edit', $term->id],
										['class' => 'btn btn-primary btn-sm'],
									) ?>
									<?= $this->Html->link(
										__d('translate', 'View String'),
										['controller' => 'TranslateStrings', 'action' => 'view', $term->translate_string_id],
										['class' => 'btn btn-secondary btn-sm'],
									) ?>
								</div>
							</div>
						</div>
					</div>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
	<?php } ?>
</div>

<!-- Recently Imported -->
<?php if (!empty($recentImports)) { ?>
<div class="row mb-2">
	<div class="col-12">
		<div class="card">
			<div class="card-header bg-primary text-white py-2">
				<h6 class="mb-0"><i class="fas fa-file-import"></i> <?= __d('translate', 'Recently Imported Strings') ?> <small class="text-white-50">(Last 30 days)</small></h6>
			</div>
			<div class="card-body p-0">
				<div class="list-group list-group-flush">
					<?php foreach (array_slice($recentImports, 0, 5) as $string) { ?>
					<div class="list-group-item py-2">
						<div class="d-flex justify-content-between align-items-start">
							<div class="flex-grow-1">
								<small>
									<strong><?= $this->Text->truncate(h($string->name), 50) ?></strong>
									<?php if ($string->plural) { ?>
										<span class="badge bg-info ms-1" title="<?= __d('translate', 'Has plural form') ?>">P</span>
									<?php } ?>
									<?php if ($string->is_html) { ?>
										<span class="badge bg-warning ms-1" title="<?= __d('translate', 'Contains HTML') ?>">HTML</span>
									<?php } ?>
								</small>
								<br>
								<small class="text-muted">
									<i class="fas fa-folder"></i> <?= h($string->translate_domain->name) ?>
									<?php if ($string->last_import) { ?>
										&middot;
										<i class="fas fa-calendar"></i> <?= $this->Time->format($string->last_import, 'yyyy-MM-dd') ?>
										<span class="text-muted">(<?= $this->Time->timeAgoInWords($string->last_import) ?>)</span>
									<?php } ?>
								</small>
							</div>
							<div>
								<?= $this->Html->link(
									'<i class="fas fa-language"></i>',
									['controller' => 'TranslateStrings', 'action' => 'translate', $string->id],
									['escape' => false, 'class' => 'btn btn-sm btn-success', 'title' => __d('translate', 'Translate')],
								) ?>
							</div>
						</div>
					</div>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
</div>
<?php } ?>

<!-- Audit Log -->
<?php if (!empty($auditLogs)) { ?>
<div class="row mb-2">
	<div class="col-12">
		<div class="card">
			<div class="card-header bg-secondary text-white py-2">
				<h6 class="mb-0"><i class="fas fa-history"></i> <?= __d('translate', 'Audit Trail') ?></h6>
			</div>
			<div class="card-body p-0">
				<div class="table-responsive">
					<table class="table table-sm table-hover mb-0">
						<thead>
							<tr>
								<th><?= __d('translate', 'Time') ?></th>
								<th><?= __d('translate', 'Type') ?></th>
								<th><?= __d('translate', 'Source') ?></th>
								<th><?= __d('translate', 'ID') ?></th>
								<th><?= __d('translate', 'Actions') ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach (array_slice($auditLogs, 0, 5) as $log) { ?>
							<tr>
								<td><small><?= $this->Time->timeAgoInWords($log->created) ?></small></td>
								<td>
									<?php if ($log->type === 'created') { ?>
										<span class="badge bg-success"><?= __d('translate', 'Created') ?></span>
									<?php } elseif ($log->type === 'updated') { ?>
										<span class="badge bg-info"><?= __d('translate', 'Updated') ?></span>
									<?php } else { ?>
										<span class="badge bg-danger"><?= __d('translate', 'Deleted') ?></span>
									<?php } ?>
								</td>
								<td><small><?= h($log->source) ?></small></td>
								<td><small><?= h($log->primary_key) ?></small></td>
								<td>
									<button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#auditModal<?= $log->id ?>">
										<i class="fas fa-eye"></i>
									</button>
								</td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Audit Log Detail Modals -->
	<?php foreach (array_slice($auditLogs, 0, 5) as $log) { ?>
		<?php
		// Parse the original and changed data
		$oldData = !empty($log->original) ? json_decode($log->original, true) : null;
		$newData = !empty($log->changed) ? json_decode($log->changed, true) : null;
		?>
	<div class="modal fade" id="auditModal<?= $log->id ?>" tabindex="-1">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h6 class="modal-title">
						<i class="fas fa-history"></i>
						<?= h($log->source) ?> #<?= h($log->primary_key) ?> -
						<?php if ($log->type === 'created') { ?>
							<span class="badge bg-success"><?= __d('translate', 'Created') ?></span>
						<?php } elseif ($log->type === 'updated') { ?>
							<span class="badge bg-info"><?= __d('translate', 'Updated') ?></span>
						<?php } else { ?>
							<span class="badge bg-danger"><?= __d('translate', 'Deleted') ?></span>
						<?php } ?>
					</h6>
					<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
				</div>
				<div class="modal-body">
					<!-- Audit Details -->
					<div class="card mb-3">
						<div class="card-header py-1 bg-light">
							<small><strong><?= __d('translate', 'Audit Information') ?></strong></small>
						</div>
						<div class="card-body p-2">
							<table class="table table-sm table-bordered mb-0">
								<tr>
									<th style="width: 30%;"><?= __d('translate', 'Timestamp') ?></th>
									<td><?= $this->Time->nice($log->created) ?></td>
								</tr>
								<tr>
									<th><?= __d('translate', 'Transaction') ?></th>
									<td><code><?= h($log->transaction) ?></code></td>
								</tr>
								<?php if (class_exists('\AuditStash\Model\Table\AuditLogsTable')) { ?>
								<tr>
									<th><?= __d('translate', 'Full Details') ?></th>
									<td>
										<?= $this->Html->link(
											'<i class="fas fa-external-link-alt"></i> ' . __d('translate', 'View in AuditStash'),
											['plugin' => 'AuditStash', 'controller' => 'AuditLogs', 'action' => 'view', $log->id],
											['escape' => false, 'class' => 'btn btn-sm btn-outline-primary'],
										) ?>
									</td>
								</tr>
								<?php } ?>
							</table>
						</div>
					</div>

					<!-- Unified Diff Display -->
						<?php if ($log->type === 'updated' && $oldData && $newData) { ?>
					<div class="card mb-3">
						<div class="card-header py-1 bg-light">
							<small><strong><?= __d('translate', 'Unified Diff') ?></strong></small>
						</div>
						<div class="card-body p-0">
							<pre class="mb-0" style="background: #f8f9fa; padding: 10px; border: none; font-size: 12px; line-height: 1.4;"><code><?php
							// Generate unified diff for each changed field
							foreach ($newData as $field => $newValue) {
								$oldValue = $oldData[$field] ?? null;

								// Skip if values are the same
								if ($oldValue === $newValue) {
									continue;
								}

								// Show field header
								echo '<span style="color: #999;">@@ ' . h($field) . ' @@</span>' . "\n";

								// Convert to string for comparison
								$oldStr = is_array($oldValue) ? json_encode($oldValue) : (string)$oldValue;
								$newStr = is_array($newValue) ? json_encode($newValue) : (string)$newValue;

								// Simple line-by-line diff
								$oldLines = explode("\n", $oldStr);
								$newLines = explode("\n", $newStr);

								$maxLines = max(count($oldLines), count($newLines));
								for ($i = 0; $i < $maxLines; $i++) {
									$oldLine = $oldLines[$i] ?? '';
									$newLine = $newLines[$i] ?? '';

									if ($oldLine !== $newLine) {
										if ($oldLine !== '') {
											echo '<span style="background: #ffdddd; color: #d00;">-' . h($oldLine) . '</span>' . "\n";
										}
										if ($newLine !== '') {
											echo '<span style="background: #ddffdd; color: #080;">+' . h($newLine) . '</span>' . "\n";
										}
									} else {
										echo ' ' . h($oldLine) . "\n";
									}
								}
								echo "\n";
							}
							?></code></pre>
						</div>
					</div>
					    <?php } ?>

					<!-- Before/After Tables -->
						<?php if ($log->type === 'updated' && $oldData && $newData) { ?>
					<div class="card">
						<div class="card-header py-1 bg-light">
							<small><strong><?= __d('translate', 'Changed Fields') ?></strong></small>
						</div>
						<div class="card-body p-2">
							<table class="table table-sm table-bordered mb-0">
								<thead>
									<tr>
										<th style="width: 20%;"><?= __d('translate', 'Field') ?></th>
										<th style="width: 40%;"><?= __d('translate', 'Before') ?></th>
										<th style="width: 40%;"><?= __d('translate', 'After') ?></th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ($newData as $field => $newValue) { ?>
										<?php
										$oldValue = $oldData[$field] ?? null;
										// Only show changed fields
										if ($oldValue === $newValue) {
											continue;
										}
										?>
										<tr>
											<th><?= h($field) ?></th>
											<td>
												<small>
													<?php if (is_array($oldValue)) { ?>
														<code><?= h(json_encode($oldValue)) ?></code>
													<?php } else { ?>
														<?= h($oldValue) ?>
													<?php } ?>
												</small>
											</td>
											<td>
												<small>
													<?php if (is_array($newValue)) { ?>
														<code><?= h(json_encode($newValue)) ?></code>
													<?php } else { ?>
														<?= h($newValue) ?>
													<?php } ?>
												</small>
											</td>
										</tr>
									<?php } ?>
								</tbody>
							</table>
						</div>
					</div>
					    <?php } elseif ($log->type === 'created' && $newData) { ?>
					<div class="card">
						<div class="card-header py-1 bg-light">
							<small><strong><?= __d('translate', 'Created Data') ?></strong></small>
						</div>
						<div class="card-body p-2">
							<table class="table table-sm table-bordered mb-0">
								<thead>
									<tr>
										<th style="width: 30%;"><?= __d('translate', 'Field') ?></th>
										<th style="width: 70%;"><?= __d('translate', 'Value') ?></th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ($newData as $field => $value) { ?>
									<tr>
										<th><?= h($field) ?></th>
										<td>
											<small>
												<?php if (is_array($value)) { ?>
													<code><?= h(json_encode($value)) ?></code>
												<?php } else { ?>
													<?= h($value) ?>
												<?php } ?>
											</small>
										</td>
									</tr>
									<?php } ?>
								</tbody>
							</table>
						</div>
					</div>
					    <?php } ?>
				</div>
			</div>
		</div>
	</div>
	<?php } ?>
<?php } ?>

<!-- Plugin Status & Quick Actions -->
<div class="row">
	<!-- Optional Plugins/Tools Status -->
	<div class="col-md-4">
		<div class="card">
			<div class="card-header bg-info text-white py-2">
				<h6 class="mb-0"><i class="fas fa-puzzle-piece"></i> <?= __d('translate', 'Available Features') ?></h6>
			</div>
			<div class="card-body p-2">
				<table class="table table-sm mb-0">
					<tbody>
						<!-- AuditStash Plugin -->
						<tr>
							<td>
								<i class="fas fa-history"></i> <?= __d('translate', 'Audit Logging') ?>
								<?= $this->Html->link(
									'<i class="fas fa-question-circle"></i>',
									'https://github.com/lorenzo/audit-stash',
									['escape' => false, 'target' => '_blank', 'class' => 'text-muted', 'title' => __d('translate', 'AuditStash Plugin')],
								) ?>
							</td>
							<td class="text-end">
								<?php if (class_exists('\AuditStash\AuditStashPlugin')) { ?>
									<?php if (Configure::read('Translate.disableAuditLog')) { ?>
										<span class="badge bg-warning text-dark" title="<?= __d('translate', 'Plugin installed but disabled in config') ?>">
											<i class="fas fa-pause"></i> <?= __d('translate', 'Disabled') ?>
										</span>
									<?php } else { ?>
										<span class="badge bg-success">
											<i class="fas fa-check"></i> <?= __d('translate', 'Active') ?>
										</span>
									<?php } ?>
								<?php } else { ?>
									<span class="badge bg-secondary">
										<i class="fas fa-times"></i> <?= __d('translate', 'Not Installed') ?>
									</span>
								<?php } ?>
							</td>
						</tr>

						<!-- Search Plugin -->
						<tr>
							<td>
								<i class="fas fa-search"></i> <?= __d('translate', 'Advanced Search') ?>
								<?= $this->Html->link(
									'<i class="fas fa-question-circle"></i>',
									'https://github.com/FriendsOfCake/search',
									['escape' => false, 'target' => '_blank', 'class' => 'text-muted', 'title' => __d('translate', 'FriendsOfCake/Search Plugin')],
								) ?>
							</td>
							<td class="text-end">
								<?php if (class_exists('\Search\Model\Behavior\SearchBehavior')) { ?>
									<span class="badge bg-success">
										<i class="fas fa-check"></i> <?= __d('translate', 'Active') ?>
									</span>
								<?php } else { ?>
									<span class="badge bg-secondary">
										<i class="fas fa-times"></i> <?= __d('translate', 'Not Installed') ?>
									</span>
								<?php } ?>
							</td>
						</tr>

						<!-- Translation API -->
						<tr>
							<td>
								<i class="fas fa-language"></i> <?= __d('translate', 'Auto-Translation API') ?>
								<small class="text-muted d-block"><?= __d('translate', 'Google Translate, DeepL, etc.') ?></small>
							</td>
							<td class="text-end">
								<?php
								$translatorClass = '\Translate\Translator\Translator';
								$hasTranslator = class_exists($translatorClass);
								?>
								<?php if ($hasTranslator) { ?>
									<span class="badge bg-success">
										<i class="fas fa-check"></i> <?= __d('translate', 'Available') ?>
									</span>
								<?php } else { ?>
									<span class="badge bg-secondary">
										<i class="fas fa-times"></i> <?= __d('translate', 'Not Available') ?>
									</span>
								<?php } ?>
							</td>
						</tr>

					</tbody>
				</table>
			</div>
		</div>
	</div>

	<!-- Quick Actions -->
	<div class="col-md-8">
		<div class="card">
			<div class="card-header py-2">
				<h6 class="mb-0">
					<a class="text-decoration-none" data-bs-toggle="collapse" href="#actionsCollapse">
						<i class="fas fa-bolt"></i> <?= __d('translate', 'Quick Actions') ?>
						<i class="fas fa-chevron-down float-end"></i>
					</a>
				</h6>
			</div>
			<div id="actionsCollapse" class="collapse show">
				<div class="card-body p-0">
					<div class="list-group list-group-flush">
						<?= $this->Html->link(
							'<i class="fas fa-file-import"></i> ' . __d('translate', 'Import from PO/POT files'),
							['controller' => 'TranslateStrings', 'action' => 'extract'],
							['escape' => false, 'class' => 'list-group-item list-group-item-action'],
						) ?>
						<?= $this->Html->link(
							'<i class="fas fa-file-export"></i> ' . __d('translate', 'Export to PO files'),
							['controller' => 'TranslateStrings', 'action' => 'dump'],
							['escape' => false, 'class' => 'list-group-item list-group-item-action'],
						) ?>
						<?= $this->Html->link(
							'<i class="fas fa-folder-open"></i> ' . __d('translate', 'Import locales from filesystem'),
							['controller' => 'TranslateLocales', 'action' => 'fromLocale'],
							['escape' => false, 'class' => 'list-group-item list-group-item-action'],
						) ?>
						<?= $this->Html->link(
							'<i class="fas fa-language"></i> ' . __d('translate', 'TranslateBehavior & Shadow Tables'),
							['controller' => 'TranslateBehavior', 'action' => 'index'],
							['escape' => false, 'class' => 'list-group-item list-group-item-action'],
						) ?>
						<?= $this->Html->link(
							'<i class="fas fa-unlink"></i> ' . __d('translate', 'Orphaned Strings'),
							['controller' => 'TranslateStrings', 'action' => 'orphaned'],
							['escape' => false, 'class' => 'list-group-item list-group-item-action'],
						) ?>
						<?= $this->Html->link(
							'<i class="fas fa-info-circle"></i> ' . __d('translate', 'Best Practices'),
							['action' => 'bestPractice'],
							['escape' => false, 'class' => 'list-group-item list-group-item-action'],
						) ?>
						<?php if (Configure::read('debug')) { ?>
							<?= $this->Html->link(
								'<i class="fas fa-exclamation-triangle text-danger"></i> ' . __d('translate', 'Reset Project Data'),
								['controller' => 'Translate', 'action' => 'reset'],
								['escape' => false, 'class' => 'list-group-item list-group-item-action list-group-item-danger'],
							) ?>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
