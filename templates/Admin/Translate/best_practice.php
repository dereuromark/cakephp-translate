<?php
/**
 * @var \App\View\AppView $this
 */
?>

<div class="row">
	<aside class="col-md-3">
		<div class="card">
			<div class="card-header">
				<h3 class="card-title"><i class="fas fa-bars"></i> <?= __d('translate', 'Actions') ?></h3>
			</div>
			<div class="list-group list-group-flush">
				<?= $this->Html->link('<i class="fas fa-tachometer-alt"></i> ' . __d('translate', 'Overview'), ['action' => 'index'], ['escape' => false, 'class' => 'list-group-item list-group-item-action']) ?>
			</div>
		</div>
	</aside>

	<div class="col-md-9">
		<div class="card">
			<div class="card-header">
				<h3 class="card-title"><i class="fas fa-book"></i> <?= __d('translate', 'bestPractice') ?></h3>
			</div>
			<div class="card-body">
				<h4><?= __d('translate', 'Common things') ?></h4>
				<ul>
					<li><?= __d('translate', 'Always code in English (not only the code - all translation strings should be that way as well)') ?></li>
					<li><?= __d('translate', 'Use short terminology for sentences and longer translations - camelCased ideally: "breadcrumpTrail"') ?></li>
					<li><?= __d('translate', 'Group them together by their meaning, like "valErr..." for Validation Errors, or "textWhy.." for some long text/explanation on the website') ?></li>
				</ul>
				<p><?= __d('translate', 'With these tips you can easily use the translate plugin to generate your translation base - and let translaters do their job.') ?></p>
			</div>
		</div>
	</div>
</div>
