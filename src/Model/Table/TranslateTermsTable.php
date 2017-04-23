<?php
namespace Translate\Model\Table;

use Cake\Log\Log;
use Tools\Model\Table\Table;

/**
 * @property \Translate\Model\Table\TranslateStringsTable|\Cake\ORM\Association\BelongsTo $TranslateStrings
 * @property \Translate\Model\Table\TranslateLanguagesTable|\Cake\ORM\Association\BelongsTo $TranslateLanguages
 *
 * @method \Translate\Model\Entity\TranslateTerm get($primaryKey, $options = [])
 * @method \Translate\Model\Entity\TranslateTerm newEntity($data = null, array $options = [])
 * @method \Translate\Model\Entity\TranslateTerm[] newEntities(array $data, array $options = [])
 * @method \Translate\Model\Entity\TranslateTerm|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Translate\Model\Entity\TranslateTerm patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \Translate\Model\Entity\TranslateTerm[] patchEntities($entities, array $data, array $options = [])
 * @method \Translate\Model\Entity\TranslateTerm findOrCreate($search, callable $callback = null, $options = [])
 * @mixin \Shim\Model\Behavior\NullableBehavior
 */
class TranslateTermsTable extends Table {

	/**
	 * @var array
	 */
	public $order = ['modified' => 'DESC'];

	/**
	 * @var string
	 */
	public $displayField = 'content';

	/**
	 * @var array
	 */
	public $validate = [
		'string_id' => ['numeric'],
		'comment' => [
		],
		'content' => [
			'isUnique' => [
				'rule' => ['validateUnique', ['scope' => ['translate_string_id', 'translate_language_id']]],
				'message' => 'valErrRecordNameExists',
				'provider' => 'table',
				'allowEmpty' => true,
			],
			'validPlaceholders' => [
				'rule' => ['validatePlaceholders'],
				'message' => 'Please confirm that you have the same amount of placeholders in your translation.',
				'provider' => 'table',
				'allowEmpty' => true,
			],
		],
		'plural_2' => [
			'validPlaceholders' => [
				'rule' => ['validatePlaceholders'],
				'message' => 'Please confirm that you have the same amount of placeholders in your translation.',
				'provider' => 'table',
				'allowEmpty' => true,
			],
		],
		'language_id' => [
			'numeric' => [
				'rule' => ['numeric'],
				'message' => 'valErrMandatoryField',
				'last' => true
			],
			'isUnique' => [
				'rule' => ['validateUnique', ['scope' => ['translate_string_id']]],
				'message' => 'valErrRecordNameExists',
				'provider' => 'table',
			],
		],
		'user_id' => ['notEmpty'],
		'confirmed' => ['numeric'],
		'confirmed_by' => ['notEmpty']
	];

	/**
	 * @var array
	 */
	public $belongsTo = [
		'TranslateString' => [
			'className' => 'Translate.TranslateString',
		],
		'TranslateLanguage' => [
			'className' => 'Translate.TranslateLanguage',
		],
		/*
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => '',
			'fields' => array('id', 'username'),
			'order' => ''
		),
		'ConfirmedBy' => array(
			'className' => 'User',
			'foreignKey' => 'confirmed_by',
			'conditions' => '',
			'fields' => array('id', 'username'),
			'order' => ''
		)*/
	];

	/**
	 * @param string $text
	 * @param array $context
	 *
	 * @return bool
	 */
	public function validatePlaceholders($text, array $context) {
		if (empty($context['data']['string'])) {
			return true;
		}

		preg_match_all('/\{\d\}/', $context['data']['string'], $expectedMatches);

		preg_match_all('/\{\d\}/', $text, $matches);
		if (!$expectedMatches && !$matches) {
			return true;
		}

		$expected = !empty($expectedMatches[0]) ? $expectedMatches[0] : [];
		$is = !empty($matches[0]) ? $matches[0] : [];

		if (count($expected) !== count($is)) {
			return false;
		}

		foreach ($expected as $key => $placeholder) {
			if (in_array($placeholder, $is)) {
				unset($expected[$key]);
			}
		}

		if ($expected) {
			return false;
		}

		return true;
	}

	/**
	 * @param array $config
	 *
	 * @return void
	 */
	public function initialize(array $config) {
		parent::initialize($config);

		$this->addBehavior('Shim.Nullable');
	}

	/**
	 * @param array $translation
	 * @param int $translateStringId
	 * @param int $translateLanguageId
	 * @return \Translate\Model\Entity\TranslateTerm
	 */
	public function import(array $translation, $translateStringId, $translateLanguageId) {
		$translation += [
			//'user_id' => null,
			'translate_string_id' => $translateStringId,
			'translate_language_id' => $translateLanguageId,
		];

		$translateTerm = $this->find()->where([
			'content IS' => $translation['content'],
			'translate_string_id' => $translateStringId,
			'translate_language_id' => $translateLanguageId,
		])->first();
		if (!$translateTerm) {
			$translateTerm = $this->newEntity($translation);
		} else {
			$translateTerm = $this->patchEntity($translateTerm, $translation);
		}

		if (!$this->save($translateTerm)) {
			Log::write('info', 'Term `' . $translateTerm->content . '` for String # `' . $translateStringId . '`: ' . print_r($translateTerm->errors(), true), ['scope' => 'import']);

			return null;
		}

		return $translateTerm;
	}

	/**
	 * @param int $languageId
	 *
	 * @param int|array|null $groupId
	 *
	 * @return \Cake\ORM\Query
	 */
	public function getTranslations($languageId, $groupId = null) {
		$options = [
			'conditions' => [$this->alias() . '.translate_language_id' => $languageId],
			'contain' => ['TranslateStrings'],
		];
		if ($groupId) {
			$options['conditions']['TranslateStrings.translate_group_id IN'] = $groupId;
		}

		return $this->find('all', $options);
	}

	/**
	 * @param int $stringId
	 *
	 * @return \Translate\Model\Entity\TranslateTerm[]
	 */
	public function getTranslatedArray($stringId) {
		$terms = $this->getTranslated($stringId);

		$array = [];
		foreach ($terms as $term) {
			$array[$term->translate_language_id] = $term;
		}

		return $array;
	}

	/**
	 * @param int $stringId
	 *
	 * @return \Cake\ORM\Query
	 */
	public function getTranslated($stringId) {
		$options = ['conditions' => [$this->alias() . '.translate_string_id' => $stringId]];

		return $this->find('all', $options);
	}

	/**
	 * @param array $data
	 *
	 * @return bool|\Translate\Model\Entity\TranslateTerm
	 */
	public function process(array $data) {
		$translateTerm = $this->newEntity($data);

		return $this->save($translateTerm);
	}

}
