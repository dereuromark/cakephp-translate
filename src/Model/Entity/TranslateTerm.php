<?php

namespace Translate\Model\Entity;

use Tools\Model\Entity\Entity;

/**
 * TranslateTerm Entity
 *
 * @property int $id
 * @property string $content
 * @property string|null $comment
 * @property bool $confirmed
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \Translate\Model\Entity\TranslateString $translate_string
 * @property \Translate\Model\Entity\TranslateLocale $translate_locale
 * @property int $translate_string_id
 * @property int $translate_locale_id
 * @property string|null $plural_2
 * @property int|null $user_id
 * @property int|null $confirmed_by
 */
class TranslateTerm extends Entity {

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
