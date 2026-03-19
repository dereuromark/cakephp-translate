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
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \Translate\Model\Table\TranslateTermsTable&\Cake\ORM\Association\HasMany $TranslateTerms
 * @property \Translate\Model\Table\TranslateDomainsTable&\Cake\ORM\Association\BelongsTo $TranslateDomains
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
 * @extends \Cake\ORM\Table<array{AuditLog: \AuditStash\Model\Behavior\AuditLogBehavior, Nullable: \Translate\Model\Behavior\NullableBehavior, Search: \Search\Model\Behavior\SearchBehavior}>
 * @mixin \AuditStash\Model\Behavior\AuditLogBehavior
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
				->where(['translate_project_id IS' => $id])
				->find('list', ['keyField' => 'id', 'valueField' => 'locale'])->toArray();
		}

		$options = [
			//'TranslateStrings.active' => true,
			'TranslateDomains.translate_project_id IS' => $id,
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
	 * Find orphaned strings (no references to source code).
	 *
	 * @param int $projectId Project ID to filter by
	 * @return \Cake\ORM\Query\SelectQuery
	 */
	public function findOrphaned(int $projectId): SelectQuery {
		return $this->find()
			->matching('TranslateDomains', function ($q) use ($projectId) {
				return $q->where([
					'TranslateDomains.translate_project_id' => $projectId,
				]);
			})
			->where([
				'OR' => [
					['TranslateStrings.references IS' => null],
					['TranslateStrings.references' => ''],
				],
			])
			->orderByDesc('TranslateStrings.modified');
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
		if (str_contains($translation['name'], '<') || str_contains($translation['name'], '>')) {
			return true;
		}
		if (empty($translation['plural'])) {
			return false;
		}
		if (str_contains($translation['plural'], '<') || str_contains($translation['plural'], '>')) {
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

			// Get API suggestions
			$translations = $translator->suggest($translateString->name, $translateLocale->locale, $baseLocale);

			// Get Translation Memory suggestions
			$memorySuggestions = $this->getSuggestionsFromMemory(
				$translateString->name,
				$translateString->translate_domain->translate_project_id,
				$translateLocale->id,
				$translateString->id,
			);

			// Merge memory suggestions with API suggestions
			foreach ($memorySuggestions as $suggestion) {
				$key = 'Memory (' . $suggestion['similarity'] . '%)';
				$translations[$key] = $suggestion['translation'];
			}

			$result[$translateLocale->locale] = $translations;
		}

		return $result;
	}

	/**
	 * Get translation suggestions from existing translations in the database (Translation Memory).
	 *
	 * Finds exact and fuzzy matches of similar strings within the same project
	 * and returns their existing translations for the specified locale.
	 *
	 * @param string $text The source text to find matches for
	 * @param int $projectId The project ID to search within
	 * @param int $localeId The locale ID to get translations for
	 * @param int|null $excludeStringId Optional string ID to exclude from results (current string)
	 * @param int $similarityThreshold Minimum similarity percentage for fuzzy matches (default 90)
	 * @return array<array{type: string, similarity: int, original: string, translation: string}>
	 */
	public function getSuggestionsFromMemory(
		string $text,
		int $projectId,
		int $localeId,
		?int $excludeStringId = null,
		int $similarityThreshold = 90,
	): array {
		$suggestions = [];
		$textLength = strlen($text);

		// Skip very short strings (less likely to have meaningful matches)
		if ($textLength < 3) {
			return [];
		}

		// Query for potential matches within ±20% length (for fuzzy matching performance)
		$minLength = (int)($textLength * 0.8);
		$maxLength = (int)($textLength * 1.2);

		$query = $this->find()
			->select([
				'TranslateStrings.id',
				'TranslateStrings.name',
			])
			->innerJoinWith('TranslateDomains', function ($q) use ($projectId) {
				return $q->where(['TranslateDomains.translate_project_id' => $projectId]);
			})
			->innerJoinWith('TranslateTerms', function ($q) use ($localeId) {
				return $q->where([
					'TranslateTerms.translate_locale_id' => $localeId,
					'TranslateTerms.content IS NOT' => null,
					'TranslateTerms.content !=' => '',
				]);
			})
			->contain([
				'TranslateTerms' => function ($q) use ($localeId) {
					return $q->where(['TranslateTerms.translate_locale_id' => $localeId]);
				},
			]);

		if ($excludeStringId !== null) {
			$query->where(['TranslateStrings.id !=' => $excludeStringId]);
		}

		/** @var array<\Translate\Model\Entity\TranslateString> $strings */
		$strings = $query->toArray();

		foreach ($strings as $string) {
			$stringName = $string->name;
			$stringLength = strlen($stringName);

			// Exact match
			if ($stringName === $text) {
				foreach ($string->translate_terms as $term) {
					$suggestions[] = [
						'type' => 'exact',
						'similarity' => 100,
						'original' => $stringName,
						'translation' => $term->content,
					];
				}

				continue;
			}

			// Fuzzy match - only check strings within length range
			if ($stringLength < $minLength || $stringLength > $maxLength) {
				continue;
			}

			$similarity = $this->calculateSimilarity($text, $stringName);
			if ($similarity >= $similarityThreshold) {
				foreach ($string->translate_terms as $term) {
					$suggestions[] = [
						'type' => 'fuzzy',
						'similarity' => $similarity,
						'original' => $stringName,
						'translation' => $term->content,
					];
				}
			}
		}

		// Sort by similarity (highest first)
		usort($suggestions, function ($a, $b) {
			return $b['similarity'] <=> $a['similarity'];
		});

		// Limit results to prevent overwhelming the UI
		return array_slice($suggestions, 0, 5);
	}

	/**
	 * Calculate similarity percentage between two strings using Levenshtein distance.
	 *
	 * @param string $str1 First string
	 * @param string $str2 Second string
	 * @return int Similarity percentage (0-100)
	 */
	protected function calculateSimilarity(string $str1, string $str2): int {
		$maxLength = max(strlen($str1), strlen($str2));
		if ($maxLength === 0) {
			return 100;
		}

		$distance = levenshtein($str1, $str2);

		return (int)((1 - ($distance / $maxLength)) * 100);
	}

}
