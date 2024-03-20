<?php

namespace Translate\Model\Entity;

use Tools\Model\Entity\Entity;

/**
 * TranslateApiTranslation Entity
 *
 * @property int $id
 * @property string $key
 * @property string|null $value
 * @property string $from
 * @property string $to
 * @property string $engine
 * @property \Cake\I18n\FrozenTime|null $created
 */
class TranslateApiTranslation extends Entity {

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

}
