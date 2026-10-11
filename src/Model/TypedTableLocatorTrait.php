<?php

namespace Translate\Model;

use Cake\ORM\Table;
use LogicException;

trait TypedTableLocatorTrait {

	/**
	 * @template T of \Cake\ORM\Table
	 * @param string $alias
	 * @param class-string<T> $class
	 * @return T
	 */
	protected function fetchTranslateTable(string $alias, string $class): Table {
		$table = $this->fetchTable($alias);
		if (!$table instanceof $class) {
			throw new LogicException(sprintf('Table %s must extend %s.', $alias, $class));
		}

		return $table;
	}

}
