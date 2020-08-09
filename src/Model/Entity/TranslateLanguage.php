<?php

namespace Translate\Model\Entity;

use Tools\Model\Entity\Entity;

/**
 * TranslateLanguage Entity
 *
 * @property int $id
 * @property int|null $language_id
 * @property string $name
 * @property string $iso2
 * @property string $locale
 * @property bool $active
 *
 * @property \Data\Model\Entity\Language|null $language
 * @property \Translate\Model\Entity\TranslateTerm[] $translate_terms
 * @property int $translate_project_id
 * @property bool $base
 * @property bool $primary
 * @property \Translate\Model\Entity\TranslateProject $translate_project
 */
class TranslateLanguage extends Entity {

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
