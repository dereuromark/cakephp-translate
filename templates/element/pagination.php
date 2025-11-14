<?php
/**
 * @var \App\View\AppView $this
 * @var bool $addArrows
 * @var array $options
 * @var bool $reverse
 */
if (!isset($separator)) {
	if (defined('PAGINATOR_SEPARATOR')) {
		$separator = PAGINATOR_SEPARATOR;
	} else {
		$separator = '';
	}
}

if (empty($first)) {
	$first = '<i class="fas fa-angle-double-left"></i> ' . __d('translate', 'First');
}
if (empty($last)) {
	$last = __d('translate', 'Last') . ' <i class="fas fa-angle-double-right"></i>';
}
if (empty($prev)) {
	$prev = '<i class="fas fa-angle-left"></i> ' . __d('translate', 'Previous');
}
if (empty($next)) {
	$next = __d('translate', 'Next') . ' <i class="fas fa-angle-right"></i>';
}
if (!isset($format)) {
	$format = __d('translate', 'Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total');
}
if (!empty($reverse)) {
	$tmp = $first;
	$first = $last;
	$last = $tmp;

	$tmp = $prev;
	$prev = $next;
	$next = $tmp;
}

$escape = $escape ?? false;
?>

<div class="paginator paging">
	<nav aria-label="Page navigation" class="d-flex justify-content-between align-items-center flex-wrap gap-3">
		<div class="pagination-info">
			<small class="text-muted">
				<i class="fas fa-info-circle"></i>
				<?= $this->Paginator->counter($format); ?>
			</small>
		</div>

		<ul class="pagination pagination-sm mb-0">
			<?= $this->Paginator->first($first, ['escape' => $escape]); ?>
			<?= $this->Paginator->prev($prev, ['escape' => $escape, 'disabledTitle' => false]); ?>
			<?= $this->Paginator->numbers(['escape' => $escape, 'separator' => $separator]); ?>
			<?= $this->Paginator->next($next, ['escape' => $escape, 'disabledTitle' => false]); ?>
			<?= $this->Paginator->last($last, ['escape' => $escape]); ?>
		</ul>
	</nav>
</div>
