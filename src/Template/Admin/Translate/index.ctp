<?php
/**
 * @var \App\View\AppView $this
 */
$totalCoverage =(int)$this->Translation->totalCoverage($coverage);
$totalColor = $this->Translation->getColor($totalCoverage);
?>

<div class="col-md-12">

<div style="float: right;">
<?php echo $this->element('project_switch', [])?>
</div>

<h2><?php echo __('Translate Plugin');?></h2>
	<p>
Easily manage i18n/translations from your backend.
	</p>


<table style="text-align: top"><tr>
<td>
<h3>Features</h3>

<ul>
	<li>Translate strings in all languages simultaneously</li>
	<li>Import from POT, PO files or DB</li>
	<li>Auto-Features like trim(), h(), newlines to <?php echo h('<p>/<br>'); ?></li>
	<li>Auto-Translate and Auto-Suggest with GoogleTranslateAPI (PHP/JS)</li>
	<li>Validate placeholders ({0})</li>
	<li>Prevent doublettes, missing translations, collisions</li>
	<li>Auto-Add Controller names (singular + plural)</li>
	<li>Manage in Groups (and export/enable/disable them)</li>
	<li>Creates clean pot files with all translations in usage</li>
</ul>

<h3>Todos/Ideas</h3>
<ul>
<li>Guests/Visitors can help, too (translations need approval from admins)</li>
<li>Extract from source code directly (triggering cake i18n internally)</li>
<li>Change strings in the source code directly from the plugin and correct spelling errors etc with this (all occurances will be translated ).</li>
<li>Plugin support __d() and for other __() methods</li>
</ul>
</td>

<td>

<h3>Status</h3>
	<p>
Current Translation-Coverage: <span style="color:#<?php echo $totalColor;?>;font-weight:bold"><?php echo $totalCoverage?>%</span> translated
	</p>

<?php
	if (!empty($coverage)) {
?>

<?php echo $this->element('coverage_table', [])?>
		<p>
<?php echo $count['groups']?> <?php echo $this->Html->link(__('Groups'), ['controller'=>'TranslateGroups']);?> with <?php echo $count['strings']?> <?php echo $this->Html->link(__('Strings'), ['controller'=>'TranslateStrings']);?> in <?php echo $count['languages']?> different <?php echo $this->Html->link(__('Languages'), ['controller'=>'TranslateLanguages']);?> = <?php echo $count['translations']?> <?php echo __('Translations');?>
		</p>

<?php } else { ?>

	<p>
<?php echo __('Please create a project first'); ?>: <?php echo $this->Html->link('Project Index', ['prefix' => 'admin', 'controller' => 'TranslateProjects']); ?>
	</p>

<?php } ?>


<h3>How to Translate</h3>
<ol>
<li>Select group you want to translate</li>
<li>Select language you want to translate into (or from)</li>
<li>Translate and submit the form</li>
</ol>


<h3>How to Administer</h3>
<ol>
<li>Extract Translate String via "cake i18n" console script</li>
<li>Importing them in Translate-Strings</li>
</ol>


<br/><br/>

<ul>
<li><?php echo $this->Html->link(__('Best Practice'), ['action' => 'bestPractice']);?> </li>
<li><?php echo $this->Html->link(__('Extract'), ['controller' => 'TranslateStrings', 'action' => 'extract']);?> </li>
	<li><?php echo $this->Html->link(__('Dump'), ['controller' => 'TranslateStrings', 'action' => 'dump']);?> </li>
</ul>

</td>
</tr></table>

</div>
