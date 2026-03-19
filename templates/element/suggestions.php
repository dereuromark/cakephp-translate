<?php
/**
 * @var \App\View\AppView $this
 * @var array<string, string> $suggestions
 * @var string $key
 * @var string $target
 */

if (!$suggestions) {
	return;
}

$suggestionsArray = [];
foreach ($suggestions as $engine => $suggestion) {
	$engineName = substr($engine, strrpos($engine, '\\') + 1);
	$isMemory = str_starts_with($engineName, 'Memory');
	$suggestionsArray[$suggestion][] = [
		'name' => $engineName,
		'isMemory' => $isMemory,
	];
}

$target = 'content-' . $key;

?>
<div class="form-group suggestions">
	<label class="control-label col-md-4 col-lg-3"><small><?= __d('translate', 'Suggestions') ?></small></label>
	<div class="col-md-8 col-lg-9">
		<ul>
	<?php foreach ($suggestionsArray as $suggestion => $engines) { ?>
		<li>
			<span class="suggest" rel="<?php echo h($key); ?>" title="Click to insert"><?php echo h($suggestion); ?></span>
			<small>
			<?php
			$engineLabels = [];
			foreach ($engines as $engine) {
				if ($engine['isMemory']) {
					$engineLabels[] = '<span class="badge bg-info text-dark">' . h($engine['name']) . '</span>';
				} else {
					$engineLabels[] = h($engine['name']);
				}
			}
			echo '(' . implode(', ', $engineLabels) . ')';
			?>
			</small>
		</li>
	<?php } ?>
		</ul>
	</div>
</div>

<style>
	span.suggest {
		cursor: pointer;
	}

</style>

<?php $this->append('script'); ?>
<script>
	$(function() {
		$('.suggest[rel="<?php echo $key; ?>"]').click(function() {
			var input = $('#<?php echo $target; ?>');
			var value = $(this).text();
			input.val(value);
			return false;
		});
	});

</script>
<?php $this->end();
