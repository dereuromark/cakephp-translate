<?php

namespace Translate\Model\Table;

use Exception;
use Tools\Model\Table\Table;
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
 * @extends \Tools\Model\Table\Table<array{Nullable: \Shim\Model\Behavior\NullableBehavior}>
 */
class TranslateProjectsTable extends Table {

	/**
	 * @var array
	 */
	public array $order = ['status' => 'DESC', 'default' => 'DESC', 'name' => 'ASC'];

	/**
	 * @var array<mixed>
	 */
	public $validate = [
		'name' => [
			'notEmpty' => [
				'rule' => ['notEmpty'],
				'message' => 'valErrMandatoryField',
				'last' => true,
			],
			'isUnique' => [
				'rule' => ['isUnique'],
				'message' => 'valErrMandatoryField',
				'last' => true,
			],
		],
		'type' => [
			'numeric' => [
				'rule' => ['numeric'],
				'message' => 'valErrMandatoryField',
			],
		],
		'status' => [
			'numeric' => [
				'rule' => ['numeric'],
				'message' => 'valErrMandatoryField',
			],
		],
		'default' => [
			'boolean' => [
				'rule' => ['boolean'],
				'message' => 'valErrMandatoryField',
			],
		],
	];

	/**
	 * @var array
	 */
	public $hasMany = [
		'TranslateDomain' => [
			'className' => 'Translate.TranslateDomain',
			'dependent' => true,
		],
	];

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
		//$this->TranslateTerms = TableRegistry::getTableLocator()->get('Translate.TranslateTerms');

		//$x = $this->TranslateTerms->TranslateStrings->habtmJoin;
		// recursive = 0;
		//$this->TranslateTerms->bindModel(['belongsTo' => $x], false);

		foreach ($types as $type) {
			switch ($type) {
				case 'terms':
					$options = [
						'conditions' => [
							'TranslateTerm.translate_language_id IN' => $languages,
							'TranslateDomain.translate_project_id' => $id,
						],
						'fields' => ['TranslateTerms.id', 'TranslateTerms.id'],
						'contain' => ['TranslateDomains' => ['TranslateStrings']],
					];
					# bug in deleteAll (cannot use containable/recursion)
					$res = $this->TranslateTerms->deleteAll($options['conditions']);

					/*
					die(returns($res));
					$res = $this->TranslateTerms->find('list', $options);
					foreach ($res as $r) {
						$this->TranslateTerms->delete($r);
					}
					*/
					break;
				case 'strings':
					$conditions = [
						'TranslateDomains.translate_project_id' => $id,
					];
					//$this->TranslateTerms->TranslateStrings->recursive = 0;
					//$this->TranslateTerms->TranslateStrings->bindModel(['belongsTo' => $x], false);
					//$res = $this->TranslateTerms->TranslateStrings->deleteAll($conditions);

					//die(returns($res));
					break;
				case 'groups':
					$conditions = [
						'TranslateDomain.translate_project_id' => $id,
					];
					$this->TranslateDomains->deleteAll($conditions);

					break;
				default:
					throw new Exception('Invalid type');
			}
		}
	}

}
