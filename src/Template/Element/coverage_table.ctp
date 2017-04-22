<?php
/**
 * @var \App\View\AppView $this
 */
?>
<table class="table table-striped">
<tr><th><?php echo __('Language'); ?></th><th><?php echo __('Coverage'); ?></th><th><?php echo __('Active'); ?></th></tr>
<?php foreach ($languages as $language) {

$currentCoverage = $coverage[$language['locale']];
$currentColor = $this->Translation->getColor($currentCoverage);
?>
<tr>
	<td><?php echo $this->Translation->flag($language['code']); ?> <?php echo h($language['name']); ?></td>
	<td><span style="color:#<?php echo $currentColor;?>;font-weight:bold"><?php echo $currentCoverage; ?>%</span></td>
	<td><?php echo $this->Format->yesNo($language['active'])?></td>
</tr>
<?php } ?>
</table>
