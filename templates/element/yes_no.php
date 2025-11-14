<?php
/**
 * Overwrite this element snippet locally to customize if needed.
 *
 * @var \App\View\AppView $this
 * @var bool $value
 * @var string|null $title
 */
$attr = [];
if (isset($title)) {
	$attr['title'] = $title;
	$attr['data-bs-toggle'] = 'tooltip';
}

if ($this->helpers()->has('IconSnippet')) {
	echo $this->IconSnippet->yesNo($value, [], $attr);
} elseif ($this->helpers()->has('Format')) {
	echo $this->Format->yesNo($value, [], $attr);
} else {
	if ($value) {
		$icon = '<i class="fas fa-check"></i>';
		$class = 'badge bg-success';
		$text = __d('translate', 'Yes');
	} else {
		$icon = '<i class="fas fa-times"></i>';
		$class = 'badge bg-secondary';
		$text = __d('translate', 'No');
	}

	$attrString = '';
	foreach ($attr as $key => $val) {
		$attrString .= ' ' . h($key) . '="' . h($val) . '"';
	}

	echo '<span class="' . $class . '"' . $attrString . '>' . $icon . ' ' . $text . '</span>';
}
