<?php

/**
 * @see: http://www.loc.gov/standards/iso639-2/php/code_list.php
 */

namespace Translate\Model\Table;

use ArrayObject;
use Cake\Core\Plugin;
use Cake\Event\EventInterface;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;
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
	 * @param \Cake\Validation\Validator $validator Validator instance.
	 *
	 * @return \Cake\Validation\Validator
	 */
	public function validationDefault(Validator $validator): Validator {
		$validator
			->scalar('name')
			->requirePresence('name', 'create')
			->notEmptyString('name', 'Please insert a language name');

		$validator
			->scalar('iso2')
			->requirePresence('iso2', 'create')
			->notEmptyString('iso2', 'Please insert a 2 letter ISO code')
			->add('iso2', 'validIsoCode', [
				'rule' => 'validateIsoCode',
				'provider' => 'table',
				'message' => 'Invalid ISO2 code',
			]);

		$validator
			->scalar('locale')
			->requirePresence('locale', 'create')
			->notEmptyString('locale', 'Format: xx or xx_YY');

		$validator
			->integer('language_id')
			->allowEmptyString('language_id', null, 'Not a number');

		$validator
			->boolean('active')
			->allowEmptyString('active');

		return $validator;
	}

	/**
	 * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
	 *
	 * @return \Cake\ORM\RulesChecker
	 */
	public function buildRules(RulesChecker $rules): RulesChecker {
		$rules->add($rules->isUnique(['name', 'translate_project_id'], 'valErrRecordExists'));
		$rules->add($rules->isUnique(['locale', 'translate_project_id'], 'valErrRecordExists'));

		return $rules;
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

		$this->hasMany('TranslateTerms', [
			'className' => 'Translate.TranslateTerms',
			'dependent' => true,
		]);

		if (Plugin::isLoaded('Data')) {
			$this->belongsTo('Languages', [
				'className' => 'Data.Languages',
				'foreignKey' => 'language_id',
			]);
		}

		$this->belongsTo('TranslateProjects', [
			'className' => 'Translate.TranslateProjects',
		]);
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
