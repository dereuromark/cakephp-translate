<?php

namespace Translate\Model\Entity;

use Tools\Model\Entity\Entity;

/**
 * TranslateProject Entity
 *
 * @property int $id
 * @property string $name
 * @property int $type
 * @property bool $default
 * @property int $status
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property array<\Translate\Model\Entity\TranslateDomain> $translate_domains
 */
class TranslateProject extends Entity {

	/**
	 * Fields that can be mass assigned using newEmptyEntity() or patchEntity().
	 *
	 * Note that when '*' is set to true, this allows all unspecified fields to
	 * be mass assigned. For security purposes, it is advised to set '*' to false
	 * (or remove it), and explicitly make individual fields accessible as needed.
	 *
	 * @var array<string, bool>
	 */
	protected array $_accessible = [
		'*' => true,
		'id' => false,
	];

	/**
	 * @param int|null $value
	 *
	 * @return array|string
	 */
	public static function statuses($value = null) {
		$options = [
			static::STATUS_INACTIVE => __d('translate', 'Inactive Project'),
			static::STATUS_HIDDEN => __d('translate', 'Hidden Project'),
			static::STATUS_PUBLIC => __d('translate', 'Public Project'),
		];

		return parent::enum($value, $options);
	}

	/**
	 * @var int
	 */
	public const STATUS_INACTIVE = 0;

	/**
	 * @var int
	 */
	public const STATUS_HIDDEN = 1;

	/**
	 * @var int
	 */
	public const STATUS_PUBLIC = 2;

	/**
	 * @param int|null $value
	 *
	 * @return array|string
	 */
	public static function types($value = null) {
		$options = [
			static::TYPE_APP => __d('translate', 'CakePHP App'),
			static::TYPE_PLUGIN => __d('translate', 'CakePHP Plugin'),
			static::TYPE_OTHER => __d('translate', 'Other Project'),
		];

		return parent::enum($value, $options);
	}

	/**
	 * @var int
	 */
	public const TYPE_APP = 0;

	/**
	 * @var int
	 */
	public const TYPE_PLUGIN = 1;

	//todo?
	/**
	 * @var int
	 */
	public const TYPE_OTHER = 9;

}
