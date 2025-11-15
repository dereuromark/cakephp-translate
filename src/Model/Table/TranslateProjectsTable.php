<?php

namespace Translate\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;
use Exception;
use Translate\Model\Entity\TranslateProject;

/**
 * @method \Translate\Model\Entity\TranslateProject get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \Translate\Model\Entity\TranslateProject newEntity(array $data, array $options = [])
 * @method array<\Translate\Model\Entity\TranslateProject> newEntities(array $data, array $options = [])
 * @method \Translate\Model\Entity\TranslateProject|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \Translate\Model\Entity\TranslateProject patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\Translate\Model\Entity\TranslateProject> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \Translate\Model\Entity\TranslateProject findOrCreate(\Cake\ORM\Query\SelectQuery|callable|array $search, ?callable $callback = null, array $options = [])
 * @mixin \Shim\Model\Behavior\NullableBehavior
 * @property \Cake\ORM\Association\HasMany<\Translate\Model\Table\TranslateDomainsTable> $TranslateDomains
 * @property \Translate\Model\Table\TranslateTermsTable|\Cake\ORM\Association\HasMany $TranslateTerms
 * @method \Translate\Model\Entity\TranslateProject saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \Translate\Model\Entity\TranslateProject newEmptyEntity()
 * @method \Cake\Datasource\ResultSetInterface<\Translate\Model\Entity\TranslateProject>|false saveMany(iterable $entities, array $options = [])
 * @method \Cake\Datasource\ResultSetInterface<\Translate\Model\Entity\TranslateProject> saveManyOrFail(iterable $entities, array $options = [])
 * @method \Cake\Datasource\ResultSetInterface<\Translate\Model\Entity\TranslateProject>|false deleteMany(iterable $entities, array $options = [])
 * @method \Cake\Datasource\ResultSetInterface<\Translate\Model\Entity\TranslateProject> deleteManyOrFail(iterable $entities, array $options = [])
 * @extends \Cake\ORM\Table<array{Nullable: \Shim\Model\Behavior\NullableBehavior}>
 */
class TranslateProjectsTable extends Table {

	/**
	 * @var array
	 */
	public array $order = ['status' => 'DESC', 'default' => 'DESC', 'name' => 'ASC'];

	/**
	 * @param \Cake\Validation\Validator $validator Validator instance.
	 *
	 * @return \Cake\Validation\Validator
	 */
	public function validationDefault(Validator $validator): Validator {
		$validator
			->scalar('name')
			->requirePresence('name', 'create')
			->notEmptyString('name', 'This field is required');

		$validator
			->numeric('type')
			->allowEmptyString('type', 'This field is required');

		$validator
			->numeric('status')
			->allowEmptyString('status', 'This field is required');

		$validator
			->boolean('default')
			->allowEmptyString('default', 'This field is required');

		return $validator;
	}

	/**
	 * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
	 *
	 * @return \Cake\ORM\RulesChecker
	 */
	public function buildRules(RulesChecker $rules): RulesChecker {
		$rules->add($rules->isUnique(['name'], 'This field is required'));

		return $rules;
	}

	/**
	 * @param array $config
	 *
	 * @return void
	 */
	public function initialize(array $config): void {
		parent::initialize($config);

		$this->addBehavior('Shim.Nullable');

		$this->hasMany('TranslateDomains', [
			'className' => 'Translate.TranslateDomains',
			'dependent' => true,
		]);
	}

	/**
	 * @return int|null projectId
	 */
	public function getDefaultProjectId() {
		$options = [
			'fields' => ['id'],
			'conditions' => [$this->getAlias() . '.status >' => TranslateProject::STATUS_INACTIVE],
			'order' => [$this->getAlias() . '.default' => 'DESC'],
		];
		$res = $this->find('all', $options)->first();
		if (!$res) {
			return null;
		}

		return $res->id;
	}

	/**
	 * @param int $id
	 * @param array<string> $types
	 * @param array<int> $languages
	 *
	 * @throws \Exception
	 *
	 * @return void
	 */
	public function reset($id, $types, $languages = []) {
		$translateTerms = TableRegistry::getTableLocator()->get('Translate.TranslateTerms');

		//$x = $this->TranslateTerms->TranslateStrings->habtmJoin;
		//$this->TranslateTerms->bindModel(['belongsTo' => $x], false);

		foreach ($types as $type) {
			switch ($type) {
				case 'terms':
					// Find term IDs that match the criteria (need to join through TranslateStrings -> TranslateDomains)
					$termIds = $translateTerms->find()
						->select(['TranslateTerms.id'])
						->innerJoinWith('TranslateStrings.TranslateDomains')
						->where([
							'TranslateTerms.translate_locale_id IN' => $languages,
							'TranslateDomains.translate_project_id' => $id,
						])
						->all()
						->extract('id')
						->toArray();

					if ($termIds) {
						$translateTerms->deleteAll(['id IN' => $termIds]);
					}

					break;
				case 'strings':
					// Find string IDs that match the criteria (need to join through TranslateDomains)
					$translateStrings = TableRegistry::getTableLocator()->get('Translate.TranslateStrings');
					$stringIds = $translateStrings->find()
						->select(['TranslateStrings.id'])
						->innerJoinWith('TranslateDomains')
						->where([
							'TranslateDomains.translate_project_id' => $id,
						])
						->all()
						->extract('id')
						->toArray();

					if ($stringIds) {
						$translateStrings->deleteAll(['id IN' => $stringIds]);
					}

					break;
				case 'groups':
				case 'domains':
					$this->TranslateDomains->deleteAll([
						'translate_project_id' => $id,
					]);

					break;
				default:
					throw new Exception('Invalid type');
			}
		}
	}

}
