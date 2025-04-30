<?php

/**
 * @see: http://www.loc.gov/standards/iso639-2/php/code_list.php
 */

namespace Translate\Model\Table;

use ArrayObject;
use Cake\Core\Plugin;
use Cake\Event\EventInterface;
use Tools\Model\Table\Table;

/**
 * @property \Cake\ORM\Association\HasMany<\Translate\Model\Table\TranslateTermsTable> $TranslateTerms
 *
 * @method \Translate\Model\Entity\TranslateLanguage get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \Translate\Model\Entity\TranslateLanguage newEntity(array $data, array $options = [])
 * @method array<\Translate\Model\Entity\TranslateLanguage> newEntities(array $data, array $options = [])
 * @method \Translate\Model\Entity\TranslateLanguage|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \Translate\Model\Entity\TranslateLanguage patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\Translate\Model\Entity\TranslateLanguage> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \Translate\Model\Entity\TranslateLanguage findOrCreate(\Cake\ORM\Query\SelectQuery|callable|array $search, ?callable $callback = null, array $options = [])
 * @mixin \Shim\Model\Behavior\NullableBehavior
 * @property \Cake\ORM\Association\BelongsTo<\Translate\Model\Table\TranslateProjectsTable> $TranslateProjects
 * @method \Translate\Model\Entity\TranslateLanguage saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @property \Data\Model\Table\LanguagesTable&\Cake\ORM\Association\BelongsTo $Languages
 * @method \Translate\Model\Entity\TranslateLanguage newEmptyEntity()
 * @method \Cake\Datasource\ResultSetInterface<\Translate\Model\Entity\TranslateLanguage>|false saveMany(iterable $entities, array $options = [])
 * @method \Cake\Datasource\ResultSetInterface<\Translate\Model\Entity\TranslateLanguage> saveManyOrFail(iterable $entities, array $options = [])
 * @method \Cake\Datasource\ResultSetInterface<\Translate\Model\Entity\TranslateLanguage>|false deleteMany(iterable $entities, array $options = [])
 * @method \Cake\Datasource\ResultSetInterface<\Translate\Model\Entity\TranslateLanguage> deleteManyOrFail(iterable $entities, array $options = [])
 * @extends \Tools\Model\Table\Table<array{Nullable: \Shim\Model\Behavior\NullableBehavior}>
 */
class TranslateLanguagesTable extends Table {

	/**
	 * @var array
	 */
	public array $order = ['name' => 'ASC'];

	/**
	 * @var array<mixed>
	 */
	public $validate = [
		'name' => [
			'minLength' => [
				'rule' => ['notEmpty'],
				'message' => 'Please insert a language name',
				'last' => true,
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
				'message' => 'Format: xx or xx_YY',
				'last' => true,
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
		'active' => ['boolean'],
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
			'order' => '',
		],
	];

	/**
	 * @param array $config
	 */
	public function __construct(array $config = []) {
		if (!Plugin::isLoaded('Data')) {
			unset($this->belongsTo['Language']);
		}

		parent::__construct($config);
	}

	/**
	 * Preparing the data
	 *
	 * @param \Cake\Event\EventInterface $event
	 * @param \ArrayObject $data
	 * @param \ArrayObject $options
	 * @return void
	 */
	public function beforeMarshal(EventInterface $event, ArrayObject $data, ArrayObject $options): void {
		if (isset($data['iso2'])) {
			$data['iso2'] = strtolower($data['iso2']);
		}
		if (isset($data['name'])) {
			$data['name'] = ucfirst($data['name']);
		}
		if (isset($data['locale'])) {
			$data['locale'] = strtolower($data['locale']);
			if (str_contains($data['locale'], '_')) {
				[$lang, $region] = explode('_', $data['locale'], 2);
				$data['locale'] = $lang . '_' . strtoupper($region);
			}
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
	public function initialize(array $config): void {
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
	 * @return \Translate\Model\Entity\TranslateLanguage|bool
	 */
	public function init(string $name, string $locale, string $iso2, int $projectId, array $data = []) {
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
	 * @return \Cake\ORM\Query\SelectQuery
	 */
	public function getActive($type = 'all', $options = []) {
		$defaults = ['conditions' => [$this->getAlias() . '.active' => 1]];

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
	 * @return \Cake\ORM\Query\SelectQuery
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
	 * @param array<\Translate\Model\Entity\TranslateLanguage> $translateLanguages
	 *
	 * @return string
	 */
	public function getBaseLocale(array $translateLanguages) {
		foreach ($translateLanguages as $translateLanguage) {
			if ($translateLanguage->base) {
				return $translateLanguage->locale;
			}
		}

		return 'en';
	}

}
