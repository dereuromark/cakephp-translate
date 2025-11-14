<?php
/**
 * @var \App\View\AppView $this
 * @var mixed $projectSwitchArray
 */
?>
<?php if (!empty($projectSwitchArray)) { ?>
<div class="card border-primary" style="min-width: 250px;">
	<div class="card-body p-3">
		<?php
		echo $this->Form->create(null, ['url' => ['controller' => 'TranslateProjects', 'action' => 'switchProject']]);

		$s = $this->request->getSession()->read('TranslateProject.id');
		$selected = '-1';
		if ($s) {
			$selected = $s;
		}

		echo $this->Form->control('project_switch', [
			'value' => $selected,
			'options' => $projectSwitchArray,
			'empty' => ['-1' => '- ' . __d('translate', 'pleaseSelect') . ' -'],
			'div' => false,
			'label' => [
				'text' => '<i class="fas fa-project-diagram"></i> ' . __d('translate', 'Switch Project'),
				'escape' => false,
				'class' => 'form-label fw-bold',
			],
			'class' => 'form-select form-select-sm',
			'onchange' => 'this.form.submit();',
		]);
		echo $this->Form->end();
		?>
	</div>
</div>
<?php }
