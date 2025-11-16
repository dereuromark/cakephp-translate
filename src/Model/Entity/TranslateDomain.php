<?php

namespace Translate\Model\Entity;

use Tools\Model\Entity\Entity;

/**
 * TranslateDomain Entity
 *
 * @property int $id
 * @property string $name
 * @property bool $active
 * @property int $prio
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \Translate\Model\Entity\TranslateProject|null $translate_project
 * @property array<\Translate\Model\Entity\TranslateString> $translate_strings
 * @property int $translate_project_id
 */
class TranslateDomain extends Entity {

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
