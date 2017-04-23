<?php
/**
 * @var \App\View\AppView $this
 * @var string[] $suggestions
 */
?>
<div class="form-group suggestions">
	<label class="control-label col-md-4 col-lg-3"><small><?php echo __('Suggestions'); ?></small></label>
	<div class="col-md-8 col-lg-9">
		<ul>
	<?php foreach ($suggestions as $suggestion) { ?>
		<li><span class="suggest" title="Click to insert"><?php echo h($suggestion); ?></span></li>
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
		$('.suggestions .suggest').click(function() {
			var value = $(this).text();
			var input = $('#<?php echo $target; ?>');
			input.val(value);
			return false;
		});
	});

</script>
<?php $this->end(); ?>
