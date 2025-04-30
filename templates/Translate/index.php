<?php
/**
 * @var \App\View\AppView $this
 * @var array $count
 * @var mixed $coverage
 * @var array $projectSwitchArray
 */

$totalCoverage = (int)$this->Translation->totalCoverage($coverage);
$totalColor = $this->Translation->getColor($totalCoverage);
?>

<div class="col-md-12">

<div style="float: right;">
<?php echo $this->element('project_switch', [])?>
</div>

<h2><?php echo __d('translate', 'Translate Plugin');?></h2>
	<p>
Easily manage i18n/translations from your backend.
	</p>

	<div class="row">
		<div class="col-md-6">


			<h3>Status</h3>
			<p>
				Current Translation-Coverage: <span style="color:#<?php echo $totalColor;?>;font-weight:bold"><?php echo $totalCoverage?>%</span> translated
			</p>

			<?php
			if (!empty($coverage) && is_array($count)) {
				?>

				<?php echo $this->element('coverage_table', [])?>
				<p>
					<?php echo $count['groups']?> <?php echo h(__d('translate', 'Groups'));?>
					with <?php echo $count['strings']?> <?php echo h(__d('translate', 'Strings'));?>
					in <?php echo $count['languages']?> different <?php echo h(__d('translate', 'Locales'));?>
					= <?php echo $count['translations']?> <?php echo h(__d('translate', 'Translations'));?>
				</p>

			<?php } ?>



		</div>
		<div class="col-md-6">
			<h3>How to Translate</h3>
			<ol>
				<li>Select "group" you want to translate</li>
				<li>Select language you want to translate into (or from)</li>
				<li>Translate and submit the form</li>
			</ol>

			<p><?php echo $this->Html->link('Continue translating', ['action' => 'translate'], ['class'=> 'btn btn-primary']); ?></p>

		</div>
	</div>

</div>
