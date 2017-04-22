<?php

/**
 * @see: http://www.loc.gov/standards/iso639-2/php/code_list.php
 */
namespace Translate\Model\Table;

use Cake\Core\Plugin;
use Tools\Model\Table\Table;

/**
 * @property \Translate\Model\Table\TranslateTermsTable|\Cake\ORM\Association\HasMany $TranslateTerms
 *
 * @method \Translate\Model\Entity\TranslateLanguage get($primaryKey, $options = [])
 * @method \Translate\Model\Entity\TranslateLanguage newEntity($data = null, array $options = [])
 * @method \Translate\Model\Entity\TranslateLanguage[] newEntities(array $data, array $options = [])
 * @method \Translate\Model\Entity\TranslateLanguage|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Translate\Model\Entity\TranslateLanguage patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \Translate\Model\Entity\TranslateLanguage[] patchEntities($entities, array $data, array $options = [])
 * @method \Translate\Model\Entity\TranslateLanguage findOrCreate($search, callable $callback = null, $options = [])
 */
class TranslateLanguagesTable extends Table {

	/**
	 * @var array
	 */
	public $order = ['name' => 'ASC'];

	/**
	 * @var array
	 */
	public $validate = [
		'name' => [
			'minLength' => [
				'rule' => ['notEmpty'],
				'message' => 'Please insert a language name',
				'last' => true
			],
			'isUnique' => [
				'rule' => ['validateUnique', ['scope' => ['translate_project_id']]],
				'provider' => 'table',
				'message' => 'valErrRecordExists',
			],
		],
		'locale' => [ # => abbreviation - used in find('list') as key
			'notEmpty' => [
				'rule' => ['notEmpty'],
				'message' => 'Please insert a abbreviation / folder-name',
				'last' => true
			],
			'isUnique' => [
				'rule' => ['isUnique'],
				'message' => 'valErrRecordExists',
			],
		],
		'language_id' => [
			'numeric' => [
				'rule' => ['numeric'],
				'message' => 'Not a number',
				'last' => true,
				//'allowEmpty' => true,
			],
			/*
			'isUnique' => array(
				'rule' => array('validateUnique'),
				'message' => 'valErrRecordExists',
				//'allowEmpty' => true,
				'last' => true
			),
			*/
		],
		'active' => ['numeric']
	];

	/**
	 * @var array
	 */
	public $hasMany = [
		'TranslateTerm' => [
			'className' => 'Translate.TranslateTerm',
			'dependent' => true,
		],
	];

	/**
	 * @var array
	 */
	public $belongsTo = [
		'Language' => [
			'className' => 'Data.Language',
			'foreignKey' => 'language_id',
		],
	];

	/**
	 * TranslateLanguagesTable constructor.
	 *
	 * @param array $config
	 */
	public function __construct(array $config = []) {
		if (!Plugin::loaded('Data')) {
			unset($this->belongsTo['Language']);
		}

		parent::__construct($config);
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

	public function getActive($type = 'all', $options = []) {
		$defaults = ['conditions' => [$this->alias() . '.active' => 1]];

		$options = array_merge($defaults, $options);
		return $this->find($type, $options);
	}

	/**
	 * @param int $translateProjectId
	 *
	 * @return array
	 */
	public function getExtractableAsList($translateProjectId) {
		return $this->getExtractable($translateProjectId)
			->find('list', ['keyField' => 'iso2', 'valueField' => 'id'])
			->toArray();
	}

	/**
	 * @param int $translateProjectId
	 *
	 * @return \Cake\ORM\Query
	 */
	public function getExtractable($translateProjectId) {
		return $this->find()
			->where([
				'translate_project_id' => $translateProjectId,
			]);
	}

	/**
	 * @param bool $inactiveOnesAsWell
	 *
	 * @return array
	 */
	public function getAsList($inactiveOnesAsWell = false) {
		$query = $this->find();
		if (!$inactiveOnesAsWell) {
			$query->where(['active' => true]);
		}

		return $query->find('list', ['keyField' => 'locale', 'valueField' => 'name'])->toArray();
	}

}
