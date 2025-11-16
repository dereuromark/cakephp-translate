<?php
/**
 * @var \App\View\AppView $this
 * @var array $count
 * @var mixed $coverage
 * @var array $projectSwitchArray
 * @var \Translate\Model\Entity\TranslateProject|null $currentProject
 */

use Cake\Core\Configure;

$totalCoverage = (int)$this->Translation->totalCoverage($coverage);
$totalColor = $this->Translation->getColor($totalCoverage);
?>

<div class="col-md-12">

<h2><?php echo __d('translate', 'Translate Plugin');?></h2>
	<p>
Easily manage i18n/translations from your backend.
	</p>


<table style="vertical-align: top"><tr>
<td>
<h3>Features</h3>

<ul>
	<li>Translate strings in all languages simultaneously</li>
	<li>Import from POT, PO files or DB</li>
	<li>Auto-Features like trim(), h(), newlines to <?php echo h('<p>/<br>'); ?></li>
	<li>Auto-Translate and Auto-Suggest with GoogleTranslateAPI (PHP/JS)</li>
	<li>Validate placeholders ({0})</li>
	<li>Directly open the code (references) to quickly see the scope/context of the translation string.</li>
	<li>Prevent doublettes, missing translations, collisions</li>
	<li>Manage in domains (and export/enable/disable them)</li>
	<li>Creates clean pot files with all translations in usage</li>
	<li>Extract from source code directly (triggering cake i18n internally)</li>
</ul>

<h3>Todos/Ideas</h3>
<ul>
	<li>Auto-Add Controller names (singular + plural)</li>
	<li>Guests/Visitors can help, too (translations need approval from admins)</li>
	<li>Change strings in the source code directly from the plugin and correct spelling errors etc with this (all references will be translated ).</li>
	<li>Plugin support __d() and for other __d('translate', ) methods</li>
</ul>
</td>

<td>

<h3>Status</h3>

<?php if (!empty($currentProject)) { ?>
	<div class="mb-3">
		<strong><?php echo __d('translate', 'Current Project'); ?>:</strong>
		<?php echo h($currentProject->name); ?>
		<span class="ms-2">
			<?php echo $this->Html->link(
				'<i class="fas fa-edit"></i> ' . __d('translate', 'Edit'),
				['controller' => 'TranslateProjects', 'action' => 'edit', $currentProject->id],
				['escape' => false, 'class' => 'btn btn-sm btn-outline-primary']
			); ?>
			<?php echo $this->Html->link(
				'<i class="fas fa-list"></i> ' . __d('translate', 'All Projects'),
				['controller' => 'TranslateProjects', 'action' => 'index'],
				['escape' => false, 'class' => 'btn btn-sm btn-outline-secondary']
			); ?>
		</span>
	</div>
<?php } else { ?>
	<div class="mb-3">
		<?php echo $this->Html->link(
			'<i class="fas fa-plus-circle"></i> ' . __d('translate', 'Create Project'),
			['controller' => 'TranslateProjects', 'action' => 'add'],
			['escape' => false, 'class' => 'btn btn-sm btn-success']
		); ?>
	</div>
<?php } ?>

	<p>
Current Translation-Coverage: <span style="color:#<?php echo $totalColor;?>;font-weight:bold"><?php echo $totalCoverage?>%</span> translated
	</p>

<?php
if (!empty($coverage) && is_array($count)) {
	?>

	<?php echo $this->element('coverage_table', [])?>
		<p>
	<?php echo $count['domains']?> <?php echo $this->Html->link(__d('translate', 'Domains'), ['controller' => 'TranslateDomains']);?>
			with <?php echo $count['strings']?> <?php echo $this->Html->link(__d('translate', 'Strings'), ['controller' => 'TranslateStrings']);?>
			in <?php echo $count['locales']?> different <?php echo $this->Html->link(__d('translate', 'Locales'), ['controller' => 'TranslateLocales']);?>
			= <?php echo $count['translations']?> <?php echo $this->Html->link(__d('translate', 'Translations'), ['controller' => 'TranslateTerms']);?>
		</p>

<?php } elseif (count($projectSwitchArray)) { ?>

	<p style="color: red">
	<?php echo __d('translate', 'Please add locales you want to support'); ?>:
		<?php if (\Cake\Core\Plugin::isLoaded('Data') && Configure::read('Translate.languagesTable') !== false) {
			echo $this->Html->link('Locales', ['controller' => 'TranslateLocales']);
			echo ' | ';
			echo $this->Html->link('Languages', ['plugin' => 'Data', 'controller' => 'Languages']);
		} else {
			echo $this->Html->link('Locales', ['controller' => 'TranslateLocales']);
		} ?>
	</p>

<?php } else { ?>

	<p style="color: red">
		<?php echo __d('translate', 'Please create a project first'); ?>: <?php echo $this->Html->link('Projects', ['controller' => 'TranslateProjects']); ?>
	</p>

<?php } ?>

<h3>How to Translate</h3>
<ol>
	<li>Select "domain" you want to translate</li>
	<li>Select language you want to translate into (or from)</li>
	<li>Translate and submit the form</li>
</ol>

	<p><?php
		if (!empty($count['strings'])) {
			echo $this->Html->link('Continue translating', ['controller' => 'TranslateStrings', 'action' => 'translate']);
		}
	?></p>


<h3>How to Administer</h3>
<ol>
	<li>Extract Translate String via "cake i18n" console script</li>
	<li>Importing POT/PO files</li>
	<li>Export them back to PO files</li>
</ol>


<br/><br/>

<ul>
	<li><?php echo $this->Html->link(__d('translate', 'Best Practice'), ['action' => 'bestPractice']);?> </li>
	<li><?php echo $this->Html->link(__d('translate', 'Import from PO/POT'), ['controller' => 'TranslateStrings', 'action' => 'extract']);?> </li>
	<li><?php echo $this->Html->link(__d('translate', 'Dump'), ['controller' => 'TranslateStrings', 'action' => 'dump']);?> </li>
<?php if (Configure::read('debug')) { ?>
	<li><?php echo $this->Html->link(__d('translate', 'Reset'), ['controller' => 'Translate', 'action' => 'reset']);?> </li>
<?php } ?>
</ul>

</td>
</tr></table>

</div>
