<?php

namespace Translate\Model\Filter;

use Cake\ORM\Query\SelectQuery;
use Search\Model\Filter\FilterCollection;

class TranslateStringsCollection extends FilterCollection {

	/**
	 * @return void
	 */
	public function initialize(): void {
		$this
			->add('translate_domain_id', 'Search.Value')
			->add('domain', 'Search.Callback', [
				'callback' => function (SelectQuery $query, array $args, $filter) {
					if (empty($args['domain'])) {
						return false;
					}

					$query->where(['TranslateDomains.name' => $args['domain']]);

					return true;
				},
			])
			->add('skipped', 'Search.Boolean')
			->add('is_html', 'Search.Boolean')
			->add('missing_translation', 'Search.Callback', [
				'callback' => function (SelectQuery $query, array $args, $filter) {
					if (!isset($args['missing_translation']) || $args['missing_translation'] === '') {
						return false;
					}

					if ($args['missing_translation']) {
						// Show only strings WITH missing translations
						$query->leftJoinWith('TranslateTerms')
							->where(['TranslateTerms.content IS' => null]);
					} else {
						// Show only strings that HAVE translations
						$query->innerJoinWith('TranslateTerms', function ($q) {
							return $q->where([
								'TranslateTerms.content IS NOT' => null,
								'TranslateTerms.content !=' => '',
							]);
						})
						->group(['TranslateStrings.id']);
					}

					return true;
				},
			])
			->add('has_plural', 'Search.Callback', [
				'callback' => function (SelectQuery $query, array $args, $filter) {
					if (!isset($args['has_plural'])) {
						return false;
					}

					if ($args['has_plural']) {
						$query->where(['TranslateStrings.plural IS NOT' => null]);
					} else {
						$query->where(['TranslateStrings.plural IS' => null]);
					}

					return true;
				},
			])
			->add('search', 'Search.Like', [
				'fields' => ['TranslateStrings.name', 'TranslateStrings.plural', 'TranslateStrings.context'],
			]);
	}

}
