<?php

namespace Translate\Model\Table;

use Cake\Validation\Validator;
use Tools\Model\Table\Table;

/**
 * TranslateApiTranslations Model
 *
 * @method \Translate\Model\Entity\TranslateApiTranslation get($primaryKey, $options = [])
 * @method \Translate\Model\Entity\TranslateApiTranslation newEntity(array $data, array $options = [])
 * @method \Translate\Model\Entity\TranslateApiTranslation[] newEntities(array $data, array $options = [])
 * @method \Translate\Model\Entity\TranslateApiTranslation|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Translate\Model\Entity\TranslateApiTranslation patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \Translate\Model\Entity\TranslateApiTranslation[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \Translate\Model\Entity\TranslateApiTranslation findOrCreate($search, ?callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @mixin \Shim\Model\Behavior\NullableBehavior
 * @method \Translate\Model\Entity\TranslateApiTranslation saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Translate\Model\Entity\TranslateApiTranslation newEmptyEntity()
 * @method \Translate\Model\Entity\TranslateApiTranslation[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \Translate\Model\Entity\TranslateApiTranslation[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \Translate\Model\Entity\TranslateApiTranslation[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \Translate\Model\Entity\TranslateApiTranslation[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
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
			->allowEmpty('id', 'create');

		$validator
			->requirePresence('key', 'create')
			->notEmpty('key');

		$validator
			->requirePresence('value', 'create');
			//->notEmpty('value');

		$validator
			->requirePresence('engine', 'create')
			->notEmpty('engine');

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
