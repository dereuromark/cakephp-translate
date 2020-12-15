<?php
$class = 'info';
if (!empty($params['class'])) {
	$class .= ' ' . $params['class'];
}
if (!isset($params['escape']) || $params['escape'] !== false) {
	$message = h($message);
}

?>
<div class="custom-<?= $class ?>"><?= $message; ?></div>
