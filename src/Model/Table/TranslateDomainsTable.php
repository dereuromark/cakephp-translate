<?php

namespace Translate\Model\Table;

use Tools\Model\Table\Table;

/**
 * @property \Translate\Model\Table\TranslateProjectsTable|\Cake\ORM\Association\BelongsTo $TranslateProjects
 * @property \Translate\Model\Table\TranslateStringsTable|\Cake\ORM\Association\HasMany $TranslateStrings
 *
 * @method \Translate\Model\Entity\TranslateDomain get($primaryKey, $options = [])
 * @method \Translate\Model\Entity\TranslateDomain newEntity($data = null, array $options = [])
 * @method \Translate\Model\Entity\TranslateDomain[] newEntities(array $data, array $options = [])
 * @method \Translate\Model\Entity\TranslateDomain|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Translate\Model\Entity\TranslateDomain patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \Translate\Model\Entity\TranslateDomain[] patchEntities($entities, array $data, array $options = [])
 * @method \Translate\Model\Entity\TranslateDomain findOrCreate($search, callable $callback = null, $options = [])
 * @mixin \Shim\Model\Behavior\NullableBehavior
 * @method \Translate\Model\Entity\TranslateDomain|bool saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 */
class TranslateDomainsTable extends Table {

	/**
	 * @var array
	 */
	public $order = ['prio' => 'DESC'];

	/**
	 * @var array
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
	public function initialize(array $config) {
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
	public function statistics($id, array $languages = null) {
		if ($languages === null) {
			$languages = $this->TranslateStrings->TranslateTerms->TranslateLanguages->find('list', ['contain' => []])->toArray();
		}

		$count = [];
		$count['groups'] = $this->find('list', ['fields' => [$this->alias() . '.id'], 'conditions' => [$this->alias() . '.translate_project_id' => $id]])->toArray();
		// recursive = 0;
		//$this->TranslateStrings->bindModel(['belongsTo' => $this->TranslateStrings->habtmJoin], false);
		$count['strings'] = $this->TranslateStrings->find()->where(['TranslateDomains.translate_project_id' => $id])->contain(['TranslateDomains'])->count();
		$count['languages'] = count($languages);
		$count['translations'] = $count['strings'] * $count['languages'];
		$count['groups'] = count($count['groups']);

		return $count;
	}

}
