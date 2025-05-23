<?php

namespace Translate\Model\Table;

use ArrayObject;
use Cake\Database\Schema\TableSchemaInterface;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\Http\Exception\InternalErrorException;
use Cake\I18n\DateTime;
use Cake\Log\Log;
use Cake\ORM\Query\SelectQuery;
use Tools\Model\Table\Table;
use Translate\Translator\Translator;

/**
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\UsersTable> $Users
 * @property \Cake\ORM\Association\HasMany<\Translate\Model\Table\TranslateTermsTable> $TranslateTerms
 * @property \Cake\ORM\Association\BelongsTo<\Translate\Model\Table\TranslateDomainsTable> $TranslateDomains
 *
 * @method \Translate\Model\Entity\TranslateString get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \Translate\Model\Entity\TranslateString newEntity(array $data, array $options = [])
 * @method array<\Translate\Model\Entity\TranslateString> newEntities(array $data, array $options = [])
 * @method \Translate\Model\Entity\TranslateString|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \Translate\Model\Entity\TranslateString patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\Translate\Model\Entity\TranslateString> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \Translate\Model\Entity\TranslateString findOrCreate(\Cake\ORM\Query\SelectQuery|callable|array $search, ?callable $callback = null, array $options = [])
 * @mixin \Shim\Model\Behavior\NullableBehavior
 * @mixin \Search\Model\Behavior\SearchBehavior
 * @method \Translate\Model\Entity\TranslateString saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \Translate\Model\Entity\TranslateString newEmptyEntity()
 * @method \Cake\Datasource\ResultSetInterface<\Translate\Model\Entity\TranslateString>|false saveMany(iterable $entities, array $options = [])
 * @method \Cake\Datasource\ResultSetInterface<\Translate\Model\Entity\TranslateString> saveManyOrFail(iterable $entities, array $options = [])
 * @method \Cake\Datasource\ResultSetInterface<\Translate\Model\Entity\TranslateString>|false deleteMany(iterable $entities, array $options = [])
 * @method \Cake\Datasource\ResultSetInterface<\Translate\Model\Entity\TranslateString> deleteManyOrFail(iterable $entities, array $options = [])
 * @extends \Tools\Model\Table\Table<array{Nullable: \Shim\Model\Behavior\NullableBehavior, Search: \Search\Model\Behavior\SearchBehavior}>
 */
class TranslateStringsTable extends Table {

	/**
	 * @var array
	 */
	public array $order = ['name' => 'ASC'];

	/**
	 * @var \Cake\I18n\DateTime|null
	 */
	protected $lastImported;

	/**
	 * @var array<mixed>
	 */
	public $validate = [
		'name' => [
			'unique' => [
				'rule' => ['validateUnique', ['scope' => ['translate_domain_id', 'context']]],
				'provider' => 'table',
				'message' => 'This name is already in use',
			],
			'minLength' => [
				'rule' => ['minLength', 1],
				'message' => 'Should have at least 1 characters',
			],
		],
		'user_id' => [
			'notEmpty' => [
				'rule' => ['notEmpty'],
				'message' => 'valErrMandatoryField',
			],
		],
		'translate_domain_id' => [
			'numeric' => [
				'rule' => ['numeric'],
				'message' => 'valErrMandatoryField',
			],
		],
	];

	/**
	 * @var array
	 */
	public $belongsTo = [
		'User' => [
			'className' => 'User',
			'foreignKey' => 'user_id',
		],

	];

	/**
	 * @var array
	 */
	public $hasMany = [
		'TranslateTerm' => [
			'className' => 'Translate.TranslateTerm',
			'dependent' => true,
		],
	];

	/**
	 * @return \Cake\Database\Schema\TableSchemaInterface
	 */
	public function getSchema(): TableSchemaInterface {
		$schema = parent::getSchema();
		$schema->setColumnType('flags', 'json');

		return $schema;
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
		$this->belongsTo('TranslateDomains', [
			'className' => 'Translate.TranslateDomains',
		]);
	}

	/**
	 * @param \Cake\Event\EventInterface $event The beforeSave event that was fired
	 * @param \Translate\Model\Entity\TranslateString $entity The entity that is going to be saved
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
	 * @return \Search\Manager
	 */
	public function searchManager() {
		$searchManager = $this->behaviors()->Search->searchManager();
		$searchManager
			->value('translate_domain_id', [
			])
			->callback('missing_translation', [
				'callback' => function (SelectQuery $query, array $args, $filter) {
					if (empty($args['missing_translation'])) {
						return false;
					}

					$query->leftJoinWith('TranslateTerms')
						->where(['TranslateTerms.content IS' => null]);

					return true;
				},
				'filterEmpty' => true,
			])
			->like('search', [
				'fields' => [$this->aliasField('name'), $this->aliasField('plural'), $this->aliasField('context')],
			]);

		return $searchManager;
	}

	/**
	 * @param int $id
	 * @param array|null $languages Languages list: [id => ...]
	 *   (defaults to ALL languages)
	 * @return array coverage
	 */
	public function coverage($id, ?array $languages = null) {
		if (!$id) {
			return [];
		}

		$res = [];
		if ($languages === null) {
			$languages = $this->TranslateTerms->TranslateLanguages->find()
				->where(['translate_project_id' => $id])
				->find('list', ['keyField' => 'id', 'valueField' => 'locale'])->toArray();
		}

		$options = [
			//'TranslateStrings.active' => true,
			'TranslateDomains.translate_project_id' => $id,
		];
		$total = $this->find()->contain(['TranslateDomains'])->where($options)->count();

		foreach ($languages as $key => $lang) {
			$options = [
				'TranslateTerms.translate_language_id' => $key,
				'TranslateTerms.content IS NOT' => null,
				//'TranslateTerms.flags' => en-not-needed
			];
			$translated = $this->TranslateTerms->find()->where($options)->count();

			$res[$lang] = $this->_coverage($total, $translated);
		}

		return $res;
	}

	/**
	 * @param int $total
	 * @param int $translated
	 *
	 * @return int
	 */
	protected function _coverage($total, $translated) {
		if ($total < 1) {
			return 0;
		}

		return (int)(($translated / $total) * 100);
	}

	/**
	 * Get next string that needs to be worked on
	 *
	 * @param int|null $domainId
	 * @param int|null $stringId
	 * @param array $options
	 *
	 * @return \Cake\ORM\Query\SelectQuery
	 */
	public function getNext(?int $domainId, ?int $stringId, array $options = []): SelectQuery {
		$conditions = [
				'TranslateStrings.skipped' => false,
		];
		if ($domainId) {
			$conditions['TranslateStrings.translate_domain_id'] = $domainId;
		}
		if ($stringId) {
			$conditions['TranslateStrings.id'] = $stringId;
		}

		$options = ['conditions' => $conditions] + $options;
		$query = $this->find('all', ...$options);
		$query->leftJoinWith('TranslateTerms');
		$query->andWhere(['TranslateTerms.content IS' => null]);

		return $query;
	}

	/**
	 * Get next string that needs to be worked on
	 *
	 * @return \Cake\ORM\Query\SelectQuery
	 */
	public function getUntranslated() {
		$query = $this->find();
		$query->leftJoinWith('TranslateTerms');

		$conditions = [
			'OR' => [
				['TranslateTerms.content IS' => null],
				['TranslateStrings.plural IS NOT' => null, 'TranslateTerms.plural_2 IS' => null],
			],
		];
		$query->where($conditions);

		return $query;
	}

	/**
	 * @param int $translateLanguageId
	 * @param array<\Translate\Model\Entity\TranslateLanguage> $translateLanguages
	 *@throws \Cake\Http\Exception\InternalErrorException
	 * @return string
	 */
	public function resolveLanguageKey(int $translateLanguageId, array $translateLanguages) {
		foreach ($translateLanguages as $translateLanguage) {
			if ($translateLanguage->id === $translateLanguageId) {
				return strtolower($translateLanguage->locale);
			}
		}

		throw new InternalErrorException('Locale not found');
	}

	/**
	 * @param array $translation
	 * @param int $groupId
	 * @return \Translate\Model\Entity\TranslateString|null
	 */
	public function import(array $translation, int $groupId) {
		if (!isset($this->lastImported)) {
			$this->lastImported = new DateTime();
		}

		$translation += [
			'last_imported' => $this->lastImported,
			'is_html' => $this->containsHtml($translation),
			'translate_domain_id' => $groupId,
		];

		$translateString = $this->find()->where([
			'name' => $translation['name'],
			//'plural' => isset($translation['plural']) ? $translation['plural'] : null,
			'context IS' => $translation['context'] ?? null,
			'translate_domain_id' => $groupId,
		])->first();
		if (!$translateString) {
			$translation['active'] = true;
			$translateString = $this->newEntity($translation);
		} else {
			$translateString = $this->patchEntity($translateString, $translation);
		}

		if (!$this->save($translateString)) {
			Log::write('info', 'String `' . $translateString->name . '`: ' . print_r($translateString->getErrors(), true), ['scope' => 'import']);

			return null;
		}

		return $translateString;
	}

	/**
	 * @param array $translation
	 *
	 * @return bool
	 */
	protected function containsHtml(array $translation) {
		if (strpos($translation['name'], '<') !== false || strpos($translation['name'], '>') !== false) {
			return true;
		}
		if (empty($translation['plural'])) {
			return false;
		}
		if (strpos($translation['plural'], '<') !== false || strpos($translation['plural'], '>') !== false) {
			return true;
		}

		return false;
	}

	/**
	 * @param \Translate\Model\Entity\TranslateString $translateString
	 * @param array<\Translate\Model\Entity\TranslateLanguage> $translateLanguages
	 * @param array<\Translate\Model\Entity\TranslateTerm> $translateTerms
	 *
	 * @return array
	 */
	public function getSuggestions($translateString, array $translateLanguages, array $translateTerms) {
		$translator = new Translator();

		$baseLocale = $this->TranslateTerms->TranslateLanguages->getBaseLocale($translateLanguages);

		$result = [];
		foreach ($translateLanguages as $translateLanguage) {
			if ($translateLanguage->locale === $baseLocale) {
				continue;
			}

			$translations = $translator->suggest($translateString->name, $translateLanguage->locale, $baseLocale);
			$result[$translateLanguage->locale] = $translations;
		}

		return $result;
	}

}
