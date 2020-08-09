<?php

namespace Translate\Model\Entity;

use Tools\Model\Entity\Entity;

/**
 * TranslateString Entity
 *
 * @property int $id
 * @property string $name
 * @property string $comments
 * @property string|null $references
 * @property bool $active
 * @property bool $is_html
 * @property \Cake\I18n\FrozenDate|null $last_import
 * @property \Cake\I18n\FrozenTime|null $created
 * @property \Cake\I18n\FrozenTime|null $modified
 *
 * @property \App\Model\Entity\User|null $user
 * @property \Translate\Model\Entity\TranslateTerm[] $translate_terms
 * @property \Translate\Model\Entity\TranslateDomain $translate_domain
 * @property string|null $plural
 * @property string|null $context
 * @property int|null $user_id
 * @property int $translate_domain_id
 * @property array|null $flags
 * @property string|null $comment
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
		'id' => false,
	];

}
