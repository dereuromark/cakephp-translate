<?php

namespace Translate\Model\Filter;

use Search\Model\Filter\FilterCollection;

class TranslateTermsCollection extends FilterCollection {

	/**
	 * @return void
	 */
	public function initialize(): void {
		$this
			->add('translate_locale_id', 'Search.Value')
			->add('search', 'Search.Like', [
				'fields' => ['TranslateTerms.content', 'TranslateStrings.name'],
			]);
	}

}
