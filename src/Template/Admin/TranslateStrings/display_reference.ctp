<?php
/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateLanguage[] $translateLanguages
 * @var \Translate\Model\Entity\TranslateString $translateString
 * @var array $fileArray
 * @var mixed $lines
 */
?>

<div class="code-excerpt">
	<pre>
<?php
$max = count($fileArray);
foreach ($lines as $k => $line) {
	$line--;

	$start = max($line - 3, 0);
	$end = min($line + 3, $max - 1);
	for($i = $start; $i <= $end; $i++) {
		$class = '';
		if ($i === $line) {
			$class = ' class="highlight"';
		}
		echo '<span' . $class . '>' . h($fileArray[$i]) . '</span>';
	}

	if ($k !== count($lines) - 1) {
		echo '<br /><br />';
	}
}
?>
		</pre>
</div>

<style>
	span.highlight {
		background-color: yellow;
	}
</style>
