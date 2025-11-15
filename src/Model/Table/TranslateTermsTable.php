<?php

namespace Translate\Model\Table;

use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\Log\Log;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * @property \Cake\ORM\Association\BelongsTo<\Translate\Model\Table\TranslateStringsTable> $TranslateStrings
 * @property \Cake\ORM\Association\BelongsTo<\Translate\Model\Table\TranslateLocalesTable> $TranslateLocales
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
 * @extends \Cake\ORM\Table<array{Nullable: \Shim\Model\Behavior\NullableBehavior, Search: \Search\Model\Behavior\SearchBehavior}>
 */
class TranslateTermsTable extends Table {

	/**
	 * @var array
	 */
	public array $order = ['modified' => 'DESC'];

	/**
	 * @var string
	 */
	public string $displayField = 'content';

	/**
	 * Custom validation rule to check if placeholders match between original and translation
	 *
	 * @param string $text
	 * @param array $context
	 *
	 * @return bool
	 */
	public function validatePlaceholders($text, array $context) {
		if (empty($context['data']['string'])) {
			return true;
		}

		$originalString = $context['data']['string'];

		// Check {0}, {1}, etc. style placeholders
		preg_match_all('/\{\d+\}/', $originalString, $expectedBraceMatches);
		preg_match_all('/\{\d+\}/', $text, $braceMatches);

		if (!$this->validatePlaceholderSet($expectedBraceMatches[0], $braceMatches[0])) {
			return false;
		}

		// Check %s, %d, %f, etc. style placeholders (sprintf format)
		// Matches: %s, %d, %f, %1$s, %2$d, etc.
		preg_match_all('/%(?:\d+\$)?[sdfboxXeEgGcup]/', $originalString, $expectedSprintfMatches);
		preg_match_all('/%(?:\d+\$)?[sdfboxXeEgGcup]/', $text, $sprintfMatches);

		if (!$this->validatePlaceholderSet($expectedSprintfMatches[0], $sprintfMatches[0])) {
			return false;
		}

		return true;
	}

	/**
	 * Validates that two sets of placeholders match
	 *
	 * @param array $expected Expected placeholders from original string
	 * @param array $actual Actual placeholders from translation
	 * @return bool
	 */
	protected function validatePlaceholderSet(array $expected, array $actual): bool {
		if (!$expected && !$actual) {
			return true;
		}

		if (count($expected) !== count($actual)) {
			return false;
		}

		// Check each expected placeholder exists in actual
		$actualCopy = $actual;
		foreach ($expected as $placeholder) {
			$key = array_search($placeholder, $actualCopy, true);
			if ($key === false) {
				return false;
			}
			unset($actualCopy[$key]);
		}

		return true;
	}

	/**
	 * @param \Cake\Validation\Validator $validator Validator instance.
	 *
	 * @return \Cake\Validation\Validator
	 */
	public function validationDefault(Validator $validator): Validator {
		$validator
			->numeric('translate_string_id')
			->allowEmptyString('translate_string_id');

		$validator
			->allowEmptyString('comment');

		$validator
			->allowEmptyString('content')
			->add('content', 'validPlaceholders', [
				'rule' => 'validatePlaceholders',
				'provider' => 'table',
				'message' => 'Translation must contain the same placeholders as the original string (e.g., {0}, %s, %d).',
			]);

		$validator
			->allowEmptyString('plural_2')
			->add('plural_2', 'validPlaceholders', [
				'rule' => 'validatePlaceholders',
				'provider' => 'table',
				'message' => 'Translation must contain the same placeholders as the original string (e.g., {0}, %s, %d).',
			]);

		$validator
			->numeric('translate_locale_id')
			->requirePresence('translate_locale_id', 'create')
			->notEmptyString('translate_locale_id', 'This field is required');

		$validator
			->allowEmptyString('user_id');

		$validator
			->numeric('confirmed')
			->allowEmptyString('confirmed');

		$validator
			->allowEmptyString('confirmed_by');

		return $validator;
	}

	/**
	 * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
	 *
	 * @return \Cake\ORM\RulesChecker
	 */
	public function buildRules(RulesChecker $rules): RulesChecker {
		$rules->add($rules->isUnique(['content', 'translate_string_id', 'translate_locale_id'], 'valErrRecordNameExists'));

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
		$this->addBehavior('Search.Search');

		$this->belongsTo('TranslateStrings', [
			'className' => 'Translate.TranslateStrings',
		]);
		$this->belongsTo('TranslateLocales', [
			'className' => 'Translate.TranslateLocales',
		]);
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
	}

	/**
	 * @param \Search\Model\Filter\FilterCollection $filterCollection
	 *
	 * @return \Search\Model\Filter\FilterCollection
	 */
	public function filterCollection($filterCollection) {
		$filterCollection
			->add('translate_locale_id', 'Search.Value')
			->add('search', 'Search.Like', [
				'fields' => [$this->aliasField('content'), 'TranslateStrings.name'],
			]);

		return $filterCollection;
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
	 * @param int $translateLocaleId
	 * @return \Translate\Model\Entity\TranslateTerm|null
	 */
	public function import(array $translation, $translateStringId, $translateLocaleId) {
		$translation += [
			//'user_id' => null,
			'translate_string_id' => $translateStringId,
			'translate_locale_id' => $translateLocaleId,
		];

		$translateTerm = $this->find()->where([
			'content IS' => $translation['content'],
			'translate_string_id' => $translateStringId,
			'translate_locale_id' => $translateLocaleId,
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
	 * @param array|int|null $domainId
	 *
	 * @return \Cake\ORM\Query\SelectQuery
	 */
	public function getTranslations($languageId, $domainId = null) {
		$options = [
			'conditions' => [$this->getAlias() . '.translate_locale_id' => $languageId],
			'contain' => ['TranslateStrings'],
		];
		if ($domainId) {
			$options['conditions']['TranslateStrings.translate_domain_id IN'] = $domainId;
		}

		return $this->find('all', $options);
	}

	/**
	 * @param int $stringId
	 *
	 * @return array<\Translate\Model\Entity\TranslateTerm>
	 */
	public function getTranslatedArray($stringId) {
		$terms = $this->getTranslated($stringId)->toArray();

		$array = [];
		/** @var \Translate\Model\Entity\TranslateTerm $term */
		foreach ($terms as $term) {
			$array[$term->translate_locale_id] = $term;
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
