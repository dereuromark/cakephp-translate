<?php
namespace Translate\Model\Table;

use Exception;
use Tools\Model\Table\Table;
use Translate\Model\Entity\TranslateProject;

/**
 * @method \Translate\Model\Entity\TranslateProject get($primaryKey, $options = [])
 * @method \Translate\Model\Entity\TranslateProject newEntity($data = null, array $options = [])
 * @method \Translate\Model\Entity\TranslateProject[] newEntities(array $data, array $options = [])
 * @method \Translate\Model\Entity\TranslateProject|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Translate\Model\Entity\TranslateProject patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \Translate\Model\Entity\TranslateProject[] patchEntities($entities, array $data, array $options = [])
 * @method \Translate\Model\Entity\TranslateProject findOrCreate($search, callable $callback = null, $options = [])
 */
class TranslateProjectsTable extends Table {

	/**
	 * @var array
	 */
	public $order = ['status' => 'DESC', 'default' => 'DESC', 'name' => 'ASC'];

	/**
	 * @var array
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
		'TranslateGroup' => [
			'className' => 'TranslateGroup',
			'dependent' => true,
		]
	];

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
	 * @return int|null projectId
	 */
	public function getDefaultProjectId() {
		$options = [
			'fields' => ['id'],
			'conditions' => [$this->alias() . '.status >' => TranslateProject::STATUS_INACTIVE],
			'order' => [$this->alias() . '.default' => 'DESC']
		];
		$res = $this->find('first', $options);
		if (!$res) {
			return null;
		}
		return $res['id'];
	}

	public function reset($id, $types, $languages = []) {
		# bug in current 2.0.4?
		//$this->TranslateTerm = TableRegistry::get('Translate.TranslateTerms');

		//$x = $this->TranslateTerms->TranslateStrings->habtmJoin;
		// recursive = 0;
		//$this->TranslateTerms->bindModel(['belongsTo' => $x], false);

		foreach ($types as $type) {
			switch ($type) {
				case 'terms':
					$options = [
						'conditions' => [
							'TranslateTerm.translate_language_id' => $languages,
							'TranslateGroup.translate_project_id' => $id,
						],
						'fields' => ['TranslateTerms.id', 'TranslateTerms.id'],
						'contain' => ['TranslateGroups' => ['TranslateStrings']]
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
						'TranslateGroups.translate_project_id' => $id,
					];
					//$this->TranslateTerms->TranslateStrings->recursive = 0;
					//$this->TranslateTerms->TranslateStrings->bindModel(['belongsTo' => $x], false);
					$res = $this->TranslateTerms->TranslateStrings->deleteAll($conditions);
					//die(returns($res));
					break;
				case 'groups':
					$conditions = [
						'TranslateGroup.translate_project_id' => $id,
					];
					$this->TranslateGroups->deleteAll($conditions);
					break;
				default:
					throw new Exception('Invalid type');
			}
		}
	}

}
