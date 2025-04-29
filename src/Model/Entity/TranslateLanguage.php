<?php

namespace Translate\Model\Entity;

use Tools\Model\Entity\Entity;

/**
 * TranslateLanguage Entity
 *
 * @property int $id
 * @property int|null $language_id
 * @property string $name
 * @property string $locale
 * @property string|null $iso2
 * @property bool $active
 *
 * @property \Data\Model\Entity\Language|null $language
 * @property array<\Translate\Model\Entity\TranslateTerm> $translate_terms
 * @property int $translate_project_id
 * @property bool $base
 * @property bool $primary
 * @property \Translate\Model\Entity\TranslateProject $translate_project
 */
class TranslateLanguage extends Entity {

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
