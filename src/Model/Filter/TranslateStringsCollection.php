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
			->add('missing_translation', 'Search.Callback', [
				'callback' => function (SelectQuery $query, array $args, $filter) {
					if (empty($args['missing_translation'])) {
						return false;
					}

					$query->leftJoinWith('TranslateTerms')
						->where(['TranslateTerms.content IS' => null]);

					return true;
				},
			])
			->add('search', 'Search.Like', [
				'fields' => ['TranslateStrings.name', 'TranslateStrings.plural', 'TranslateStrings.context'],
			]);
	}

}
