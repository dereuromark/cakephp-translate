<?php

/**
 * @var \App\View\AppView $this
 * @var \Translate\Model\Entity\TranslateLocale[] $translateLocales
 * @var \Translate\Model\Entity\TranslateString $translateString
 * @var array $fileArray
 * @var array<int> $lines
 * @var string $reference
 */
use Cake\Core\Configure;

?>

<div class="code-excerpt">
	<pre>
<?php
$max = count($fileArray);
foreach ($lines as $k => $line) {
	$line--;

	$start = max($line - 3, 0);
	$end = min($line + 3, $max - 1);
	for ($i = $start; $i <= $end; $i++) {
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

<?php if (Configure::read('Translate.onlineRepoUrl')) { ?>
	<?php
	$url = Configure::read('Translate.onlineRepoUrl') . $reference;
	if (count($lines) > 1) {
		$url .= '#L' . array_shift($lines) . '-L' . array_pop($lines);
	} else {
		$url .= '#L' . array_shift($lines);
	}
	?>
	<?php echo $this->Html->link('See online', $url, ['target' => '_blank']); ?>
<?php }
