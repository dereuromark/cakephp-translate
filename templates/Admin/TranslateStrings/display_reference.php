<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateLanguage[] $translateLanguages
 * @var \Translate\Model\Entity\TranslateString $translateString
 * @var array $fileArray
 * @var mixed $lines
 * @var string $reference
 * @var string $referencePath
 * @var bool $canEdit
 * @var string $fileContent
 */

use Cake\Core\Configure;

?>

<?php if ($canEdit): ?>
	<div class="alert alert-warning" role="alert">
		<h5 class="alert-heading">
			<i class="fas fa-exclamation-triangle"></i> <?= __d('translate', 'Debug Mode - Source Code Editing Enabled') ?>
		</h5>
		<p class="mb-2">
			<strong><?= __d('translate', 'Warning:') ?></strong>
			<?= __d('translate', 'You are about to edit the source code file directly. Make sure you have a clean working directory!') ?>
		</p>
		<button class="btn btn-sm btn-warning" type="button" data-bs-toggle="collapse" data-bs-target="#sourceEditor" aria-expanded="false" aria-controls="sourceEditor">
			<i class="fas fa-edit"></i> <?= __d('translate', 'Show Source Code Editor') ?>
		</button>
	</div>

	<div class="collapse mb-3" id="sourceEditor">
		<div class="card">
			<div class="card-header bg-dark text-white">
				<i class="fas fa-file-code"></i> <?= h($referencePath) ?>
			</div>
			<div class="card-body p-0">
				<?= $this->Form->create(null, ['type' => 'post']) ?>
				<?= $this->Form->textarea('file_content', [
					'value' => $fileContent,
					'class' => 'form-control',
					'style' => 'font-family: monospace; font-size: 12px; min-height: 400px; width: 100%; border: none; border-radius: 0;',
					'rows' => 20,
				]) ?>
				<div class="card-footer">
					<?= $this->Form->button(
						'<i class="fas fa-save"></i> ' . __d('translate', 'Save Changes'),
						['class' => 'btn btn-danger', 'escapeTitle' => false],
					) ?>
					<span class="text-muted ms-2">
						<i class="fas fa-code-branch"></i> <?= __d('translate', 'Remember to commit your changes!') ?>
					</span>
				</div>
				<?= $this->Form->end() ?>
			</div>
		</div>
	</div>
<?php endif; ?>

<h5 class="mb-3">
	<i class="fas fa-search"></i> <?= __d('translate', 'Code Excerpt') ?>
	<?php if (!$canEdit): ?>
		<small class="text-muted">(<?= h($referencePath) ?>)</small>
	<?php endif; ?>
</h5>

<div class="code-excerpt">
	<pre>
<?php
$max = count($fileArray);
foreach ($lines as $k => $line) {
	$line--;

	$start = max($line - 3, 0);
	$end = min($line + 3, $max - 1);
	for ($i = $start; $i <= $end; $i++) {
		$class = '';
		if ($i === $line) {
			$class = ' class="highlight"';
		}
		echo '<span' . $class . '>' . h($fileArray[$i]) . '</span>';
	}

	if ($k !== count($lines) - 1) {
		echo '<br /><br />';
	}
}
?>
		</pre>
</div>

<?php if (Configure::read('Translate.onlineRepoUrl')) { ?>
	<?php
	$url = Configure::read('Translate.onlineRepoUrl') . $reference;
	if (count($lines) > 1) {
		$url .= '#L' . array_shift($lines) . '-L' . array_pop($lines);
	} else {
		$url .= '#L' . array_shift($lines);
	}
	?>
	<?php echo $this->Html->link('See online', $url, ['target' => '_blank']); ?>
<?php }
