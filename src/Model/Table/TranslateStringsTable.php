<?php

namespace Translate\Model\Table;

use ArrayObject;
use Cake\Core\Configure;
use Cake\Database\Schema\TableSchemaInterface;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\Http\Exception\InternalErrorException;
use Cake\I18n\DateTime;
use Cake\Log\Log;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Translate\Model\Filter\TranslateStringsCollection;
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
 * @mixin \Translate\Model\Behavior\NullableBehavior
 * @mixin \Search\Model\Behavior\SearchBehavior
 * @method \Translate\Model\Entity\TranslateString saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \Translate\Model\Entity\TranslateString newEmptyEntity()
 * @method \Cake\Datasource\ResultSetInterface<\Translate\Model\Entity\TranslateString>|false saveMany(iterable $entities, array $options = [])
 * @method \Cake\Datasource\ResultSetInterface<\Translate\Model\Entity\TranslateString> saveManyOrFail(iterable $entities, array $options = [])
 * @method \Cake\Datasource\ResultSetInterface<\Translate\Model\Entity\TranslateString>|false deleteMany(iterable $entities, array $options = [])
 * @method \Cake\Datasource\ResultSetInterface<\Translate\Model\Entity\TranslateString> deleteManyOrFail(iterable $entities, array $options = [])
 * @extends \Cake\ORM\Table<array{Nullable: \Translate\Model\Behavior\NullableBehavior, Search: \Search\Model\Behavior\SearchBehavior}>
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
	 * @return \Cake\Database\Schema\TableSchemaInterface
	 */
	public function getSchema(): TableSchemaInterface {
		$schema = parent::getSchema();
		$schema->setColumnType('flags', 'json');

		return $schema;
	}

	/**
	 * @param \Cake\Validation\Validator $validator Validator instance.
	 *
	 * @return \Cake\Validation\Validator
	 */
	public function validationDefault(Validator $validator): Validator {
		$validator
			->scalar('name')
			->minLength('name', 1, 'Should have at least 1 characters')
			->requirePresence('name', 'create')
			->notEmptyString('name');

		$validator
			->scalar('plural')
			->allowEmptyString('plural')
			->add('plural', 'validPlaceholders', [
				'rule' => function ($value, $context) {
					if (!$value || empty($context['data']['name'])) {
						return true;
					}
					// Ensure plural has same placeholders as name
					preg_match_all('/\{\d\}/', $context['data']['name'], $nameMatches);
					preg_match_all('/\{\d\}/', $value, $pluralMatches);

					return count($nameMatches[0]) === count($pluralMatches[0]);
				},
				'message' => 'Plural form must have the same number of placeholders as the singular form',
			]);

		$validator
			->scalar('context')
			->allowEmptyString('context');

		$validator
			->allowEmptyString('user_id');

		$validator
			->numeric('translate_domain_id')
			->requirePresence('translate_domain_id', 'create')
			->notEmptyString('translate_domain_id', 'This field is required');

		return $validator;
	}

	/**
	 * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
	 *
	 * @return \Cake\ORM\RulesChecker
	 */
	public function buildRules(RulesChecker $rules): RulesChecker {
		$rules->add($rules->isUnique(['name', 'translate_domain_id', 'context'], 'This name is already in use'));

		return $rules;
	}

	/**
	 * @param array $config
	 *
	 * @return void
	 */
	public function initialize(array $config): void {
		parent::initialize($config);

		$this->addBehavior('Translate.Nullable');
		$this->addBehavior('Search.Search', [
			'collectionClass' => TranslateStringsCollection::class,
		]);

		// Add audit logging if AuditStash plugin is available and not disabled by config
		if (class_exists('\AuditStash\AuditStashPlugin') && !Configure::read('Translate.disableAuditLog')) {
			$this->addBehavior('AuditStash.AuditLog', [
				'blacklist' => ['modified', 'created'],
			]);
		}

		$this->belongsTo('Users', [
			'className' => 'Users',
			'foreignKey' => 'user_id',
		]);

		$this->belongsTo('TranslateDomains', [
			'className' => 'Translate.TranslateDomains',
		]);

		$this->hasMany('TranslateTerms', [
			'className' => 'Translate.TranslateTerms',
			'dependent' => true,
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
			$languages = $this->TranslateTerms->TranslateLocales->find()
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
				'TranslateTerms.translate_locale_id' => $key,
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
	 * @param int $translateLocaleId
	 * @param array<\Translate\Model\Entity\TranslateLocale> $translateLocales
	 *@throws \Cake\Http\Exception\InternalErrorException
	 * @return string
	 */
	public function resolveLanguageKey(int $translateLocaleId, array $translateLocales) {
		foreach ($translateLocales as $translateLocale) {
			if ($translateLocale->id === $translateLocaleId) {
				return strtolower($translateLocale->locale);
			}
		}

		throw new InternalErrorException('Locale not found');
	}

	/**
	 * @param array $translation
	 * @param int $domainId
	 * @return \Translate\Model\Entity\TranslateString|null
	 */
	public function import(array $translation, int $domainId) {
		if (!isset($this->lastImported)) {
			$this->lastImported = new DateTime();
		}

		$translation += [
			'last_imported' => $this->lastImported,
			'is_html' => $this->containsHtml($translation),
			'translate_domain_id' => $domainId,
		];

		$translateString = $this->find()->where([
			'name' => $translation['name'],
			//'plural' => isset($translation['plural']) ? $translation['plural'] : null,
			'context IS' => $translation['context'] ?? null,
			'translate_domain_id' => $domainId,
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
	 * @param array<\Translate\Model\Entity\TranslateLocale> $translateLocales
	 * @param array<\Translate\Model\Entity\TranslateTerm> $translateTerms
	 *
	 * @return array
	 */
	public function getSuggestions($translateString, array $translateLocales, array $translateTerms) {
		$translator = new Translator();

		$baseLocale = $this->TranslateTerms->TranslateLocales->getBaseLocale($translateLocales);

		$result = [];
		foreach ($translateLocales as $translateLocale) {
			if ($translateLocale->locale === $baseLocale) {
				continue;
			}

			$translations = $translator->suggest($translateString->name, $translateLocale->locale, $baseLocale);
			$result[$translateLocale->locale] = $translations;
		}

		return $result;
	}

}
