<?php

/**
 * @see: http://www.loc.gov/standards/iso639-2/php/code_list.php
 */
namespace Translate\Model\Table;

use ArrayObject;
use Cake\Core\Plugin;
use Cake\Event\Event;
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
 * @mixin \Shim\Model\Behavior\NullableBehavior
 * @property \Translate\Model\Table\TranslateProjectsTable|\Cake\ORM\Association\BelongsTo $TranslateProjects
 * @method \Translate\Model\Entity\TranslateLanguage|bool saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
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
		'iso2' => [ // For translation handling (languages)
			'notEmpty' => [
				'rule' => ['notEmpty'],
				'message' => 'Please insert a 2 letter ISO code',
				'last' => true,
			],
			'validIsoCode' => [
				'rule' => ['validateIsoCode'],
				'provider' => 'table',
				'message' => 'Invalid ISO2 code',
				'last' => true,
			],
		],
		'locale' => [ // For Locale folder import/export
			'notEmpty' => [
				'rule' => ['notEmpty'],
				'message' => 'Please insert a abbreviation / folder-name',
				'last' => true
			],
			'isUnique' => [
				'rule' => ['isUnique'],
				'provider' => 'table',
				'message' => 'valErrRecordExists',
			],
		],
		'language_id' => [
			'numeric' => [
				'rule' => ['numeric'],
				'message' => 'Not a number',
				'last' => true,
				'allowEmpty' => true,
			],
		],
		'active' => ['boolean']
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
		'TranslateProject' => [
			'className' => 'Translate.TranslateProject',
			'conditions' => '',
			'fields' => '',
			'order' => ''
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
	 * Preparing the data
	 *
	 * @param \Cake\Event\Event $event
	 * @param \ArrayObject $data
	 * @param \ArrayObject $options
	 * @return void
	 */
	public function beforeMarshal(Event $event, ArrayObject $data, ArrayObject $options) {
		if (isset($data['iso2'])) {
			$data['iso2'] = strtolower($data['iso2']);
		}
		if (isset($data['name'])) {
			$data['name'] = ucfirst($data['name']);
		}
	}

	/**
	 * @param string $value
	 *
	 * @return bool
	 */
	public function validateIsoCode($value) {
		if (strlen($value) !== 2) {
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
	 * @param string $name
	 * @param string $locale
	 * @param string $iso2
	 * @param int $projectId
	 * @param array $data
	 *
	 * @return bool|\Translate\Model\Entity\TranslateLanguage
	 */
	public function init($name, $locale, $iso2, $projectId, array $data = []) {
		$translateLanguage = $this->newEntity([
			'name' => $name,
			'locale' => $locale,
			'iso2' => $iso2,
			'translate_project_id' => $projectId,
		] + $data + ['active' => true]);

		return $this->save($translateLanguage, ['strict' => true]);
	}

	/**
	 * @param string $type
	 * @param array $options
	 *
	 * @return \Cake\ORM\Query
	 */
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

	/**
	 * @param \Translate\Model\Entity\TranslateLanguage[] $translateLanguages
	 *
	 * @return string
	 */
	public function getBaseLanguage(array $translateLanguages) {
		foreach ($translateLanguages as $translateLanguage) {
			if ($translateLanguage->base) {
				return $translateLanguage->iso2;
			}
		}

		return 'en';
	}

}
