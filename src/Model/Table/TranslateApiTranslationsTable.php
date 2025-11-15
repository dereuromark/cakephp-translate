<?php

namespace Translate\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * TranslateApiTranslations Model
 *
 * @method \Translate\Model\Entity\TranslateApiTranslation get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \Translate\Model\Entity\TranslateApiTranslation newEntity(array $data, array $options = [])
 * @method array<\Translate\Model\Entity\TranslateApiTranslation> newEntities(array $data, array $options = [])
 * @method \Translate\Model\Entity\TranslateApiTranslation|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \Translate\Model\Entity\TranslateApiTranslation patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\Translate\Model\Entity\TranslateApiTranslation> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \Translate\Model\Entity\TranslateApiTranslation findOrCreate(\Cake\ORM\Query\SelectQuery|callable|array $search, ?callable $callback = null, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @mixin \Shim\Model\Behavior\NullableBehavior
 * @method \Translate\Model\Entity\TranslateApiTranslation saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \Translate\Model\Entity\TranslateApiTranslation newEmptyEntity()
 * @method \Cake\Datasource\ResultSetInterface<\Translate\Model\Entity\TranslateApiTranslation>|false saveMany(iterable $entities, array $options = [])
 * @method \Cake\Datasource\ResultSetInterface<\Translate\Model\Entity\TranslateApiTranslation> saveManyOrFail(iterable $entities, array $options = [])
 * @method \Cake\Datasource\ResultSetInterface<\Translate\Model\Entity\TranslateApiTranslation>|false deleteMany(iterable $entities, array $options = [])
 * @method \Cake\Datasource\ResultSetInterface<\Translate\Model\Entity\TranslateApiTranslation> deleteManyOrFail(iterable $entities, array $options = [])
 * @extends \Cake\ORM\Table<array{Nullable: \Shim\Model\Behavior\NullableBehavior}>
 */
class TranslateApiTranslationsTable extends Table {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config): void {
		parent::initialize($config);

		$this->setTable('translate_api_translations');
		$this->setDisplayField('id');
		$this->setPrimaryKey('id');

		$this->addBehavior('Shim.Nullable');
	}

	/**
	 * Default validation rules.
	 *
	 * @param \Cake\Validation\Validator $validator Validator instance.
	 * @return \Cake\Validation\Validator
	 */
	public function validationDefault(Validator $validator): Validator {
		$validator
			->add('id', 'valid', ['rule' => 'integer'])
			->allowEmptyString('id', 'create');

		$validator
			->requirePresence('key', 'create')
			->notEmptyString('key');

		$validator
			->requirePresence('value', 'create');
			//->notEmptyString('value');

		$validator
			->requirePresence('engine', 'create')
			->notEmptyString('engine');

		return $validator;
	}

	/**
	 * @param string $key
	 * @param string $value
	 * @param string $to
	 * @param string $from
	 * @param string $engine
	 *
	 * @return \Translate\Model\Entity\TranslateApiTranslation|bool
	 */
	public function store($key, $value, $to, $from, $engine) {
		$translateApiTranslation = $this->newEntity([
			'key' => $key,
			'from' => $from,
			'to' => $to,
			'value' => $value,
			'engine' => $engine,
		]);

		return $this->save($translateApiTranslation);
	}

	/**
	 * @param string $key
	 * @param string $to
	 * @param string|null $from
	 * @param string|null $engine
	 *
	 * @return \Translate\Model\Entity\TranslateApiTranslation|null
	 */
	public function retrieve($key, $to, $from, $engine = null) {
		if (!$from) {
			return null;
		}

		$query = $this->find()->where(['key' => $key, 'from' => $from, 'to' => $to]);
		if ($engine) {
			$query->andWhere(['engine' => $engine]);
		}

		/** @var \Translate\Model\Entity\TranslateApiTranslation|null $translation */
		$translation = $query->first();

		return $translation;
	}

}
