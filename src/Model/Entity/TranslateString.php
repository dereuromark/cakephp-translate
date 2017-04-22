<?php
namespace Translate\Model\Entity;

use Tools\Model\Entity\Entity;

/**
 * TranslateString Entity
 *
 * @property int $id
 * @property string $name
 * @property string $comments
 * @property string $occurrences
 * @property bool $active
 * @property bool $is_html
 * @property \Cake\I18n\Time $last_import
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\User $user
 * @property \Translate\Model\Entity\TranslateTerm[] $translate_terms
 * @property \Translate\Model\Entity\TranslateGroup $translate_group
 * @property string $plural
 * @property string $context
 * @property int $user_id
 * @property int $translate_group_id
 */
class TranslateString extends Entity {

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
