<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\Translate\Model\Entity\TranslateLocale> $translateLocales
 */

use Cake\Core\Plugin;

?>
<div class="row">
	<aside class="col-md-3">
		<div class="card">
			<div class="card-header">
				<h3 class="card-title"><i class="fa fa-bars"></i> <?= __d('translate', 'Actions') ?></h3>
			</div>
			<div class="list-group list-group-flush">
				<?= $this->Html->link(__d('translate', 'Overview'), ['controller' => 'Translate', 'action' => 'index'], ['class' => 'list-group-item list-group-item-action']) ?>
				<?= $this->Html->link(__d('translate', 'From Locale'), ['action' => 'fromLocale'], ['class' => 'list-group-item list-group-item-action']) ?>
				<?= $this->Html->link(__d('translate', 'To Locale'), ['action' => 'toLocale'], ['class' => 'list-group-item list-group-item-action']) ?>
				<?= $this->Html->link(__d('translate', 'New Locale'), ['action' => 'add'], ['class' => 'list-group-item list-group-item-action']) ?>
			</div>
		</div>
	</aside>
	<div class="col-md-9">
		<div class="card">
			<div class="card-header">
				<h3 class="card-title"><i class="fa fa-globe"></i> <?= __d('translate', 'Locales') ?></h3>
			</div>
			<div class="card-body p-0">
				<div class="table-responsive">
					<table class="table table-striped mb-0">
						<thead>
							<tr>
								<th><?= $this->Paginator->sort('language_id') ?></th>
								<th><?= $this->Paginator->sort('name') ?></th>
								<th><?= $this->Paginator->sort('locale') ?></th>
								<th><?= $this->Paginator->sort('active') ?></th>
								<th class="actions"><?= __d('translate', 'Actions') ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($translateLocales as $translateLocale) { ?>
							<tr>
								<td>
								<?php
								$flagCode = $this->Translation->resolveFlagCode($translateLocale);
								if ($flagCode) {
									echo $this->Translation->flag($flagCode);
								}
								?>

								<?= h($translateLocale->language_id) ?></td>
								<td><?= h($translateLocale->name) ?></td>
								<td><?= h($translateLocale->locale) ?></td>
								<td><?= $this->element('Translate.yes_no', ['value' => $translateLocale->active]) ?></td>
								<td class="actions">
									<div class="btn-group" role="group">
										<?= $this->Html->link($this->Icon->render('view'), ['action' => 'view', $translateLocale->id], ['escape' => false, 'class' => 'btn btn-sm btn-outline-primary', 'title' => __d('translate', 'View')]) ?>
										<?= $this->Html->link($this->Icon->render('edit'), ['action' => 'edit', $translateLocale->id], ['escape' => false, 'class' => 'btn btn-sm btn-outline-secondary', 'title' => __d('translate', 'Edit')]) ?>
										<?= $this->Form->postLink($this->Icon->render('delete'), ['action' => 'delete', $translateLocale->id], ['escape' => false, 'class' => 'btn btn-sm btn-outline-danger', 'title' => __d('translate', 'Delete'), 'confirm' => __d('translate', 'Are you sure you want to delete # {0}?', $translateLocale->id)]) ?>
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
