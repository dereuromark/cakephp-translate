<?php
/**
 * @var \App\View\AppView $this
 * @var mixed $sourceFile
 */
?>
<div class="card">
	<div class="card-header">
		<h3 class="card-title"><i class="fa-solid fa-file-code"></i> <?= __d('translate', 'Source Code') ?></h3>
	</div>
	<div class="card-body" style="max-height:500px;overflow:auto">
		<?php
		highlight_file($sourceFile);
		?>
	</div>
</div>
