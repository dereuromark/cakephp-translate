<?php
/**
 * @var \App\View\AppView $this
 */
?>

<nav class="col-md-3 col-sm-4 col-xs-12">
	<ul class="side-nav nav nav-pills nav-stacked">
		<li class="heading"><?= __d('translate', 'Actions') ?></li>
		<li><?= $this->Html->link(__d('translate', 'Overview'), ['action' => 'index']) ?></li>
	</ul>
</nav>
<div class="translateStrings form col-md-9 col-sm-8 col-xs-12">
<h2><?php echo __d('translate', 'bestPractice');?></h2>

<h3>Common things</h3>
<ul>
<li>Always code in english (not only the code - all translation strings should be that way as well)</li>
<li>Use short terminology for sentences and longer translations - camelCased ideally: "breadcrumpTrail"</li>
<li>Group them together by their meaning, like "valErr..." for Validation Errors, or "textWhy.." for some long text/explanation on the website</li>
</ul>
With these tips you can easily use the translate plugin to generate your translation base - and let translaters do their job.

<br/><br/>

</div>
