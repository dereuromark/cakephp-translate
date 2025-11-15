<?php
/**
 * @var \App\View\AppView $this
 * @var array $coverage
 * @var array<\Translate\Model\Entity\TranslateLocale> $languages
 */
?>
<table class="table table-striped">
<tr><th><?php echo __d('translate', 'Locale'); ?></th><th><?php echo __d('translate', 'Coverage'); ?></th><th><?php echo __d('translate', 'Active'); ?></th></tr>
<?php foreach ($languages as $language) {
	if (!isset($coverage[$language->locale])) {
		$currentCoverage = 0.0;
	} else {
		$currentCoverage = $coverage[$language->locale];
	}
	$currentColor = $this->Translation->getColor($currentCoverage);
	?>
<tr>
	<td><?php echo $this->Translation->flag($this->Translation->resolveFlagCode($language)); ?> <?php echo h($language->locale); ?></td>
	<td><span style="color:#<?php echo $currentColor;?>;font-weight:bold"><?php echo $currentCoverage; ?>%</span></td>
	<td><?= $this->element('Translate.yes_no', ['value' => $language['active']]) ?></td>
</tr>
<?php } ?>
</table>
