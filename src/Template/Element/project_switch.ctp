<?php
/**
 * @var \App\View\AppView $this
 */
?>
<?php if (!empty($projectSwitchArray) && count($projectSwitchArray) > 1) { ?>
<div style="border: 1px solid red; margin-left:10px; float: right">
<?php
echo $this->Form->create(null, ['url' => ['controller'=>'TranslateProjects', 'action'=>'switchProject']]);

$selected = '-1';
if ($s = $this->request->session()->read('TranslateProject.id')) {
	$selected = $s;
}

echo $this->Form->control('project_switch', ['value'=>$selected, 'options'=>$projectSwitchArray, 'empty'=>['-1'=>'- '.__d('translate', 'pleaseSelect').' -'], 'div'=>false, 'label'=>false, 'onchange'=>'submit();']);
echo $this->Form->end();
?>
</div>
<?php } ?>
