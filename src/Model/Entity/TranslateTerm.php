<?php
namespace Translate\Model\Entity;

use Tools\Model\Entity\Entity;

/**
 * TranslateTerm Entity
 *
 * @property int $id
 * @property string $content
 * @property string $comment
 * @property bool $confirmed
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \Translate\Model\Entity\TranslateString $translate_string
 * @property \Translate\Model\Entity\TranslateLanguage $translate_language
 * @property int $translate_string_id
 * @property int $translate_language_id
 * @property string $plural_2
 * @property int $user_id
 * @property int $confirmed_by
 */
class TranslateTerm extends Entity {

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
		'id' => false
	];

}
