<?php

namespace Translate\View\Helper;

use Cake\View\Helper;

/**
 * Icon Helper
 *
 * Provides simple icon rendering using Font Awesome icons
 */
class IconHelper extends Helper {

	/**
	 * Icon mapping for common actions
	 *
	 * @var array<string, string>
	 */
	protected array $icons = [
		'view' => 'fa-eye',
		'edit' => 'fa-edit',
		'delete' => 'fa-trash',
		'translate' => 'fa-language',
		'comment' => 'fa-comment',
		'add' => 'fa-plus',
		'list' => 'fa-list',
		'search' => 'fa-search',
		'save' => 'fa-save',
		'cancel' => 'fa-times',
		'download' => 'fa-download',
		'upload' => 'fa-upload',
	];

	/**
	 * Render an icon
	 *
	 * @param string $icon Icon name or Font Awesome class
	 * @param array<string, mixed> $options Additional HTML attributes
	 * @return string HTML icon element
	 */
	public function render(string $icon, array $options = []): string {
		// Check if it's a mapped icon name
		if (isset($this->icons[$icon])) {
			$iconClass = $this->icons[$icon];
		} elseif (str_starts_with($icon, 'fa-')) {
			// Already a Font Awesome class
			$iconClass = $icon;
		} else {
			// Fallback to treating as Font Awesome class
			$iconClass = 'fa-' . $icon;
		}

		// Ensure 'fas' prefix for solid icons
		$class = 'fas ' . $iconClass;

		// Merge with any additional classes from options
		if (!empty($options['class'])) {
			$class .= ' ' . $options['class'];
			unset($options['class']);
		}

		// Build attributes string
		$attributes = ['class' => $class];
		foreach ($options as $key => $value) {
			$attributes[$key] = $value;
		}

		$attributeString = '';
		foreach ($attributes as $key => $value) {
			$attributeString .= ' ' . h($key) . '="' . h($value) . '"';
		}

		return '<i' . $attributeString . '></i>';
	}

}
