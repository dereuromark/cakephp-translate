<?php

namespace Translate\Model\Table;

use Tools\Model\Table\Table;

/**
 * @property \Cake\ORM\Association\BelongsTo<\Translate\Model\Table\TranslateProjectsTable> $TranslateProjects
 * @property \Cake\ORM\Association\HasMany<\Translate\Model\Table\TranslateStringsTable> $TranslateStrings
 *
 * @method \Translate\Model\Entity\TranslateDomain get($primaryKey, $options = [])
 * @method \Translate\Model\Entity\TranslateDomain newEntity(array $data, array $options = [])
 * @method array<\Translate\Model\Entity\TranslateDomain> newEntities(array $data, array $options = [])
 * @method \Translate\Model\Entity\TranslateDomain|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Translate\Model\Entity\TranslateDomain patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\Translate\Model\Entity\TranslateDomain> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \Translate\Model\Entity\TranslateDomain findOrCreate($search, ?callable $callback = null, $options = [])
 * @mixin \Shim\Model\Behavior\NullableBehavior
 * @method \Translate\Model\Entity\TranslateDomain saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Translate\Model\Entity\TranslateDomain newEmptyEntity()
 * @method \Cake\Datasource\ResultSetInterface<\Translate\Model\Entity\TranslateDomain>|false saveMany(iterable $entities, $options = [])
 * @method \Cake\Datasource\ResultSetInterface<\Translate\Model\Entity\TranslateDomain> saveManyOrFail(iterable $entities, $options = [])
 * @method \Cake\Datasource\ResultSetInterface<\Translate\Model\Entity\TranslateDomain>|false deleteMany(iterable $entities, $options = [])
 * @method \Cake\Datasource\ResultSetInterface<\Translate\Model\Entity\TranslateDomain> deleteManyOrFail(iterable $entities, $options = [])
 */
class TranslateDomainsTable extends Table {

	/**
	 * @var array
	 */
	public array $order = ['prio' => 'DESC'];

	/**
	 * @var array<mixed>
	 */
	public $validate = [
		'name' => [
			'notEmpty',
			'isUnique' => [
				'rule' => ['validateUnique', ['scope' => ['translate_project_id']]],
				'provider' => 'table',
				'message' => 'valErrRecordExists',
			],
		],
		'active' => ['boolean'],
	];

	/**
	 * @var array
	 */
	public $belongsTo = [
		'TranslateProject' => [
			'className' => 'Translate.TranslateProject',
			'conditions' => '',
			'fields' => '',
			'order' => '',
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
		$this->hasMany('TranslateStrings', [
			'className' => 'Translate.TranslateStrings',
			'dependent' => true,
		]);
	}

	/**
	 * @return \Cake\ORM\Query
	 */
	public function getActive() {
		return $this->find()
			->where(['active' => true]);
	}

	/**
	 * @param int $projectId
	 * @param string $name
	 *
	 * @return \Translate\Model\Entity\TranslateDomain
	 */
	public function getDomain($projectId, $name = 'default') {
		$translateDomain = $this->findOrCreate([
			'name' => $name,
			'translate_project_id' => $projectId,
		]);
		// The default one should always be active
		if ($translateDomain['name'] === 'default') {
			$translateDomain->active = true;
		}
		$this->TranslateProjects->saveOrFail($translateDomain);

		return $translateDomain;
	}

	/**
	 * @param int $id
	 * @param array|null $languages
	 *
	 * @return array
	 */
	public function statistics($id, ?array $languages = null) {
		if ($languages === null) {
			$languages = $this->TranslateStrings->TranslateTerms->TranslateLanguages->find('list', ['contain' => []])->toArray();
		}

		$count = [];
		$count['groups'] = $this->find('list', ['fields' => [$this->getAlias() . '.id'], 'conditions' => [$this->getAlias() . '.translate_project_id' => $id]])->toArray();
		// recursive = 0;
		//$this->TranslateStrings->bindModel(['belongsTo' => $this->TranslateStrings->habtmJoin], false);
		$count['strings'] = $this->TranslateStrings->find()->where(['TranslateDomains.translate_project_id' => $id])->contain(['TranslateDomains'])->count();
		$count['languages'] = count($languages);
		$count['translations'] = $count['strings'] * $count['languages'];
		$count['groups'] = count($count['groups']);

		return $count;
	}

}
