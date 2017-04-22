<?php
namespace Translate\Model\Table;

use Tools\Model\Table\Table;

/**
 * @property \Translate\Model\Table\TranslateProjectsTable|\Cake\ORM\Association\BelongsTo $TranslateProjects
 * @property \Translate\Model\Table\TranslateStringsTable|\Cake\ORM\Association\HasMany $TranslateStrings
 *
 * @method \Translate\Model\Entity\TranslateGroup get($primaryKey, $options = [])
 * @method \Translate\Model\Entity\TranslateGroup newEntity($data = null, array $options = [])
 * @method \Translate\Model\Entity\TranslateGroup[] newEntities(array $data, array $options = [])
 * @method \Translate\Model\Entity\TranslateGroup|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Translate\Model\Entity\TranslateGroup patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \Translate\Model\Entity\TranslateGroup[] patchEntities($entities, array $data, array $options = [])
 * @method \Translate\Model\Entity\TranslateGroup findOrCreate($search, callable $callback = null, $options = [])
 */
class TranslateGroupsTable extends Table {

	public $order = ['prio' => 'DESC'];

	public $validate = [
		'name' => [
			'notEmpty',
			'isUnique' => [
				'rule' => ['validateUnique', ['scope' => ['translate_project_id']]],
				'provider' => 'table',
				'message' => 'valErrRecordExists',
			],
		],
		'active' => ['numeric']
	];

	public $belongsTo = [
		'TranslateProject' => [
			'className' => 'Translate.TranslateProject',
			'conditions' => '',
			'fields' => '',
			'order' => ''
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
	 * @param array $translateLanguages
	 *
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
	 * @return \Translate\Model\Entity\TranslateGroup
	 */
	public function getGroup($projectId, $name = 'default') {
		$translateGroup = $this->findOrCreate([
			'name' => $name,
			'translate_project_id' => $projectId,
		]);
		$this->TranslateProjects->saveOrFail($translateGroup);

		return $translateGroup;
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
		$count['strings'] = $this->TranslateStrings->find()->where(['TranslateGroups.translate_project_id' => $id])->contain(['TranslateGroups'])->count();
		$count['languages'] = count($languages);
		$count['translations'] = $count['strings'] * $count['languages'];
		$count['groups'] = count($count['groups']);

		return $count;
	}

}
