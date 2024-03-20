<?php
/**
 * Overwrite this element snippet locally to customize if needed.
 *
 * @var \App\View\AppView $this
 * @var bool $value
 * @var string|null $title
 */
?>
<?php
	$attr = [];
	if (isset($title)) {
		$attr['title'] = $title;
	}

	if ($this->helpers()->has('IconSnippet')) {
		echo $this->IconSnippet->yesNo($value, [], $attr);
	} elseif ($this->helpers()->has('Format')) {
		echo $this->Format->yesNo($value, [], $attr);
	} else {
		echo $value ? '<span class="yes-no yes-no-yes">Yes</span>' : '<span class="yes-no yes-no-no">No</span>';
	}
