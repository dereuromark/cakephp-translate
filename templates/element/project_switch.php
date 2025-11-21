<?php
/**
 * @var \App\View\AppView $this
 * @var mixed $projectSwitchArray
 * @var \Translate\Model\Entity\TranslateProject|null $currentProject
 */

$controller = $this->request->getParam('controller');
$action = $this->request->getParam('action');
$isHomepage = ($controller === 'Translate' && $action === 'index');
?>

<?php if ($isHomepage && !empty($projectSwitchArray)) { ?>
	<!-- Project Selector (Homepage only) -->
	<div class="project-switch-wrapper d-flex align-items-center gap-2">
		<?php
		$s = $this->request->getSession()->read('TranslateProject.id');
		$selected = '-1';
		if ($s) {
			$selected = $s;
		}
		?>
		<label class="form-label mb-0 text-white">
			<i class="fas fa-project-diagram"></i>
			<?= __d('translate', 'Project') ?>:
		</label>
		<?php
		echo $this->Form->create(null, [
			'url' => ['controller' => 'Translate', 'action' => 'switchProject'],
		]);

		echo $this->Form->control('project_switch', [
			'value' => $selected,
			'options' => $projectSwitchArray,
			'empty' => ['-1' => '- ' . __d('translate', 'pleaseSelect') . ' -'],
			'div' => false,
			'label' => false,
			'class' => 'form-select form-select-sm',
			'style' => 'min-width: 200px;',
			'onchange' => 'if (this.value !== "-1") { this.form.submit(); }',
		]);
		echo $this->Form->end();
		?>
	</div>
<?php } elseif (!empty($currentProject)) { ?>
	<!-- Current Project Display (All other pages) -->
	<div class="current-project-display d-flex align-items-center gap-2 text-white">
		<i class="fas fa-project-diagram"></i>
		<span class="fw-bold"><?= h($currentProject->name) ?></span>
	</div>
<?php }
