<?php

namespace Translate\Model\Table;

use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\Log\Log;
use Tools\Model\Table\Table;

/**
 * @property \Cake\ORM\Association\BelongsTo<\Translate\Model\Table\TranslateStringsTable> $TranslateStrings
 * @property \Cake\ORM\Association\BelongsTo<\Translate\Model\Table\TranslateLanguagesTable> $TranslateLanguages
 *
 * @method \Translate\Model\Entity\TranslateTerm get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \Translate\Model\Entity\TranslateTerm newEntity(array $data, array $options = [])
 * @method array<\Translate\Model\Entity\TranslateTerm> newEntities(array $data, array $options = [])
 * @method \Translate\Model\Entity\TranslateTerm|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \Translate\Model\Entity\TranslateTerm patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\Translate\Model\Entity\TranslateTerm> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \Translate\Model\Entity\TranslateTerm findOrCreate(\Cake\ORM\Query\SelectQuery|callable|array $search, ?callable $callback = null, array $options = [])
 * @mixin \Shim\Model\Behavior\NullableBehavior
 * @method \Translate\Model\Entity\TranslateTerm saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @mixin \Search\Model\Behavior\SearchBehavior
 * @method \Translate\Model\Entity\TranslateTerm newEmptyEntity()
 * @method \Cake\Datasource\ResultSetInterface<\Translate\Model\Entity\TranslateTerm>|false saveMany(iterable $entities, array $options = [])
 * @method \Cake\Datasource\ResultSetInterface<\Translate\Model\Entity\TranslateTerm> saveManyOrFail(iterable $entities, array $options = [])
 * @method \Cake\Datasource\ResultSetInterface<\Translate\Model\Entity\TranslateTerm>|false deleteMany(iterable $entities, array $options = [])
 * @method \Cake\Datasource\ResultSetInterface<\Translate\Model\Entity\TranslateTerm> deleteManyOrFail(iterable $entities, array $options = [])
 * @extends \Tools\Model\Table\Table<array{Nullable: \Shim\Model\Behavior\NullableBehavior, Search: \Search\Model\Behavior\SearchBehavior}>
 */
class TranslateTermsTable extends Table {

	/**
	 * @var array
	 */
	public array $order = ['modified' => 'DESC'];

	/**
	 * @var string
	 */
	public $displayField = 'content';

	/**
	 * @var array<mixed>
	 */
	public $validate = [
		'translate_string_id' => ['numeric'],
		'comment' => [
		],
		'content' => [
			'isUnique' => [
				'rule' => ['validateUnique', ['scope' => ['translate_string_id', 'translate_language_id']]],
				'message' => 'valErrRecordNameExists',
				'provider' => 'table',
				'allowEmpty' => true,
			],
			'validPlaceholders' => [
				'rule' => ['validatePlaceholders'],
				'message' => 'Please confirm that you have the same amount of placeholders in your translation.',
				'provider' => 'table',
				'allowEmpty' => true,
			],
		],
		'plural_2' => [
			'validPlaceholders' => [
				'rule' => ['validatePlaceholders'],
				'message' => 'Please confirm that you have the same amount of placeholders in your translation.',
				'provider' => 'table',
				'allowEmpty' => true,
			],
		],
		'translate_language_id' => [
			'numeric' => [
				'rule' => ['numeric'],
				'message' => 'valErrMandatoryField',
				'last' => true,
			],
		],
		'user_id' => ['notEmpty'],
		'confirmed' => ['numeric'],
		'confirmed_by' => ['notEmpty'],
	];

	/**
	 * @var array
	 */
	public $belongsTo = [
		'TranslateString' => [
			'className' => 'Translate.TranslateString',
		],
		'TranslateLanguage' => [
			'className' => 'Translate.TranslateLanguage',
		],
		/*
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => '',
			'fields' => array('id', 'username'),
			'order' => ''
		),
		'ConfirmedBy' => array(
			'className' => 'User',
			'foreignKey' => 'confirmed_by',
			'conditions' => '',
			'fields' => array('id', 'username'),
			'order' => ''
		)*/
	];

	/**
	 * @param string $text
	 * @param array $context
	 *
	 * @return bool
	 */
	public function validatePlaceholders($text, array $context) {
		if (empty($context['data']['string'])) {
			return true;
		}

		preg_match_all('/\{\d\}/', $context['data']['string'], $expectedMatches);

		preg_match_all('/\{\d\}/', $text, $matches);
		if (!$expectedMatches[0] && !$matches[0]) {
			return true;
		}

		$expected = !empty($expectedMatches[0]) ? $expectedMatches[0] : [];
		$is = !empty($matches[0]) ? $matches[0] : [];

		if (count($expected) !== count($is)) {
			return false;
		}

		foreach ($expected as $key => $placeholder) {
			if (in_array($placeholder, $is)) {
				unset($expected[$key]);
			}
		}

		if ($expected) {
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
		$this->addBehavior('Search.Search');
	}

	/**
	 * @return \Search\Manager
	 */
	public function searchManager() {
		$searchManager = $this->behaviors()->Search->searchManager();
		$searchManager
			->value('translate_language_id', [
			])
			->like('search', [
				'fields' => [$this->aliasField('content'), 'TranslateStrings.name'],
			]);

		return $searchManager;
	}

	/**
	 * @param \Cake\Event\EventInterface $event The beforeSave event that was fired
	 * @param \Translate\Model\Entity\TranslateTerm $entity The entity that is going to be saved
	 * @param \ArrayObject $options the options passed to the save method
	 * @return void
	 */
	public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void {
		$user = $event->getData('_footprint');
		if ($user) {
			$entity->user_id = $user['id'];
		}
	}

	/**
	 * @param array $translation
	 * @param int $translateStringId
	 * @param int $translateLanguageId
	 * @return \Translate\Model\Entity\TranslateTerm|null
	 */
	public function import(array $translation, $translateStringId, $translateLanguageId) {
		$translation += [
			//'user_id' => null,
			'translate_string_id' => $translateStringId,
			'translate_language_id' => $translateLanguageId,
		];

		$translateTerm = $this->find()->where([
			'content IS' => $translation['content'],
			'translate_string_id' => $translateStringId,
			'translate_language_id' => $translateLanguageId,
		])->first();
		if (!$translateTerm) {
			$translateTerm = $this->newEntity($translation);
		} else {
			$translateTerm = $this->patchEntity($translateTerm, $translation);
		}

		if (!$this->save($translateTerm)) {
			Log::write('info', 'Term `' . $translateTerm->content . '` for String # `' . $translateStringId . '`: ' . print_r($translateTerm->getErrors(), true), ['scope' => 'import']);

			return null;
		}

		return $translateTerm;
	}

	/**
	 * @param int $languageId
	 *
	 * @param array|int|null $groupId
	 *
	 * @return \Cake\ORM\Query\SelectQuery
	 */
	public function getTranslations($languageId, $groupId = null) {
		$options = [
			'conditions' => [$this->getAlias() . '.translate_language_id' => $languageId],
			'contain' => ['TranslateStrings'],
		];
		if ($groupId) {
			$options['conditions']['TranslateStrings.translate_domain_id IN'] = $groupId;
		}

		return $this->find('all', $options);
	}

	/**
	 * @param int $stringId
	 *
	 * @return array<\Translate\Model\Entity\TranslateTerm>
	 */
	public function getTranslatedArray($stringId) {
		$terms = $this->getTranslated($stringId);

		$array = [];
		foreach ($terms as $term) {
			$array[$term->translate_language_id] = $term;
		}

		return $array;
	}

	/**
	 * @param int $stringId
	 *
	 * @return \Cake\ORM\Query\SelectQuery
	 */
	public function getTranslated($stringId) {
		$options = ['conditions' => [$this->getAlias() . '.translate_string_id' => $stringId]];

		return $this->find('all', $options);
	}

	/**
	 * @param array $data
	 *
	 * @return \Translate\Model\Entity\TranslateTerm|bool
	 */
	public function process(array $data) {
		$translateTerm = $this->newEntity($data);

		return $this->save($translateTerm);
	}

}
