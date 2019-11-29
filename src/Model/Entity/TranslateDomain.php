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
 * @property string $path
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \Translate\Model\Entity\TranslateProject $translate_project
 * @property \Translate\Model\Entity\TranslateString[] $translate_strings
 * @property int $translate_project_id
 */
class TranslateDomain extends Entity {

	/**
	 * Fields that can be mass assigned using newEntity() or patchEntity().
	 *
	 * Note that when '*' is set to true, this allows all unspecified fields to
	 * be mass assigned. For security purposes, it is advised to set '*' to false
	 * (or remove it), and explicitly make individual fields accessible as needed.
	 *
	 * @var array
	 */
	protected $_accessible = [
		'*' => true,
		'id' => false,
	];

}
