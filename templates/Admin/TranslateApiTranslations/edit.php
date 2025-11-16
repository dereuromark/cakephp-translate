<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateApiTranslation $translateApiTranslation
 */
?>
<div class="row">
	<div class="col-md-3">
		<div class="card">
			<div class="card-header">
				<h3 class="card-title"><i class="fa fa-cog"></i> <?= __d('translate', 'Actions') ?></h3>
			</div>
			<div class="card-body p-0">
				<ul class="list-group list-group-flush">
					<li class="list-group-item">
						<?= $this->Form->postLink('<i class="fa fa-trash"></i> ' . __d('translate', 'Delete'), ['action' => 'delete', $translateApiTranslation->id], ['escape' => false, 'class' => 'text-danger', 'confirm' => __d('translate', 'Are you sure you want to delete # {0}?', $translateApiTranslation->id)]) ?>
					</li>
					<li class="list-group-item">
						<?= $this->Html->link('<i class="fa fa-list"></i> ' . __d('translate', 'List API Translations'), ['action' => 'index'], ['escape' => false, 'class' => '']) ?>
					</li>
				</ul>
			</div>
		</div>
	</div>

	<div class="col-md-9">
		<div class="card">
			<div class="card-header">
				<h3 class="card-title"><i class="fa fa-cloud"></i> <?= __d('translate', 'Edit API Translation') ?></h3>
			</div>
			<?= $this->Form->create($translateApiTranslation) ?>
			<div class="card-body">
				<?php
					echo $this->Form->control('key', ['label' => __d('translate', 'Key')]);
					echo $this->Form->control('value', ['label' => __d('translate', 'Value')]);
					echo $this->Form->control('from', ['label' => __d('translate', 'From')]);
					echo $this->Form->control('to', ['label' => __d('translate', 'To')]);
					echo $this->Form->control('engine', ['label' => __d('translate', 'Engine')]);
				?>
			</div>
			<div class="card-footer">
				<?= $this->Form->button('<i class="fa fa-save"></i> ' . __d('translate', 'Submit'), ['escape' => false, 'class' => 'btn btn-primary']) ?>
			</div>
			<?= $this->Form->end() ?>
		</div>
	</div>
</div>
