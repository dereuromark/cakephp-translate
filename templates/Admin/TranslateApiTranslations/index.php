<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\Translate\Model\Entity\TranslateApiTranslation> $translateApiTranslations
 */

use Cake\Core\Plugin;

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
						<?= $this->Html->link('<i class="fa fa-plus"></i> ' . __d('translate', 'New API Translation'), ['action' => 'add'], ['escape' => false, 'class' => '']) ?>
					</li>
				</ul>
			</div>
		</div>
	</div>

	<div class="col-md-9">
		<div class="card">
			<div class="card-header">
				<h3 class="card-title"><i class="fa fa-cloud"></i> <?= __d('translate', 'API Translations') ?></h3>
			</div>
			<div class="card-body p-0">
				<div class="table-responsive">
					<table class="table table-striped table-hover m-0">
						<thead>
							<tr>
								<th><?= $this->Paginator->sort('key', __d('translate', 'Key')) ?></th>
								<th><?= $this->Paginator->sort('from', __d('translate', 'From')) ?></th>
								<th><?= $this->Paginator->sort('to', __d('translate', 'To')) ?></th>
								<th><?= $this->Paginator->sort('engine', __d('translate', 'Engine')) ?></th>
								<th><?= $this->Paginator->sort('created', __d('translate', 'Created'), ['direction' => 'desc']) ?></th>
								<th class="actions"><?= __d('translate', 'Actions') ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($translateApiTranslations as $translateApiTranslation) { ?>
							<tr>
								<td><?= h($this->Text->truncate($translateApiTranslation->key)) ?></td>
								<td><?= h($translateApiTranslation->from) ?></td>
								<td><?= h($translateApiTranslation->to) ?></td>
								<td><?= h($translateApiTranslation->engine) ?></td>
								<td><?= $this->Time->nice($translateApiTranslation->created) ?></td>
								<td class="actions">
									<div class="btn-group" role="group">
										<?= $this->Html->link($this->Icon->render('view'), ['action' => 'view', $translateApiTranslation->id], ['escape' => false, 'class' => 'btn btn-sm btn-outline-primary', 'title' => __d('translate', 'View')]) ?>
										<?= $this->Html->link($this->Icon->render('edit'), ['action' => 'edit', $translateApiTranslation->id], ['escape' => false, 'class' => 'btn btn-sm btn-outline-secondary', 'title' => __d('translate', 'Edit')]) ?>
										<?= $this->Form->postLink($this->Icon->render('delete'), ['action' => 'delete', $translateApiTranslation->id], ['escape' => false, 'class' => 'btn btn-sm btn-outline-danger', 'title' => __d('translate', 'Delete'), 'confirm' => __d('translate', 'Are you sure you want to delete # {0}?', $translateApiTranslation->id)]) ?>
									</div>
								</td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</div>
			<div class="card-footer">
				<?php
				if (Plugin::isLoaded('Tools')) {
					echo $this->element('Tools.pagination');
				} else {
					echo $this->element('pagination');
				}
				?>
			</div>
		</div>
	</div>
</div>
