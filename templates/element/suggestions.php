<?php
/**
 * @var \App\View\AppView $this
 * @var array<string, string> $suggestions
 */

if (!$suggestions) {
	return;
}

$suggestionsArray = [];
foreach ($suggestions as $engine => $suggestion) {
	$suggestionsArray[$suggestion][] = substr($engine, strrpos($engine, '\\') + 1);
}

//$target = 'content-' .$key;

?>
<div class="form-group suggestions">
	<label class="control-label col-md-4 col-lg-3"><small><?php echo __('Suggestions'); ?></small></label>
	<div class="col-md-8 col-lg-9">
		<ul>
	<?php foreach ($suggestionsArray as $suggestion => $engines) { ?>
		<li><span class="suggest" rel="<?php echo $suggestion; ?>" title="Click to insert"><?php echo h($suggestion); ?></span> <small>(<?php echo implode(', ', $engines); ?>)</small></li>
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
<?php $this->end(); ?>
