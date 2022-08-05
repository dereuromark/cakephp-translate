<?php

namespace Translate\Translator;

use Cake\Core\Configure;
use Cake\Core\InstanceConfigTrait;
use Cake\ORM\TableRegistry;
use RuntimeException;
use Translate\Translator\Engine\Google;

class Translator {

	use InstanceConfigTrait;

	/**
	 * @var array
	 */
	protected $_defaultConfig = [
		'engine' => Google::class,
	];

	/**
	 * @var \Translate\Model\Table\TranslateApiTranslationsTable
	 */
	protected $_cache;

	/**
	 * @param array $config
	 */
	public function __construct(array $config = []) {
		$config += (array)Configure::read('Translate');
		$this->setConfig($config);

		$engine = $this->getConfig('engine');

		$this->_cache = TableRegistry::getTableLocator()->get('Translate.TranslateApiTranslations');
	}

	/**
	 * Returns an array of all suggestions.
	 * It will use all provided engines.
	 *
	 * @param string $text Text up to 5000 chars
	 * @param string $to Iso2 code (e.g.: de)
	 * @param string $from Iso2 code (e.g.: en)
	 *
	 * @return array
	 */
	public function suggest($text, $to, $from) {
		$engines = $this->_getEngines();

		$results = [];
		foreach ($engines as $engine) {
			$engineName = get_class($engine);
			$cacheResult = $this->_cache->retrieve($text, $to, $from, $engineName);
			if ($cacheResult) {
				if ($cacheResult->value === null) {
					continue;
				}
				$results[$engineName] = $cacheResult->value;

				continue;
			}

			$result = $engine->translate($text, $to, $from);
			$this->_cache->store($text, $result, $to, $from, $engineName);
			if ($result === null) {
				continue;
			}

			$results[get_class($engine)] = $result;
		}

		return $results;
	}

	/**
	 * Uses the engines in order, and returns a result
	 * as soon as the first engine provides one.
	 *
	 * @param string $text Text up to 5000 chars
	 * @param string $to Iso2 code (e.g.: de)
	 * @param string $from Iso2 code (e.g.: en)
	 *
	 * @return string|null
	 */
	public function translate($text, $to, $from) {
		$engines = $this->_getEngines();

		foreach ($engines as $engine) {
			$engineName = get_class($engine);
			$cacheResult = $this->_cache->retrieve($text, $to, $from, $engineName);
			if ($cacheResult) {
				if ($cacheResult->value === null) {
					continue;
				}

				return $cacheResult->value;
			}

			$result = $engine->translate($text, $to, $from);
			$this->_cache->store($text, $result, $to, $from, $engineName);
			if ($result !== null) {
				return $result;
			}
		}

		return null;
	}

	/**
	 * @return array<\Translate\Translator\EngineInterface>
	 */
	protected function _getEngines() {
		$engineClasses = (array)$this->getConfig('engine');

		$engines = [];
		foreach ($engineClasses as $engineClass) {
			$engine = new $engineClass($this->getConfig());
			if (!($engine instanceof EngineInterface)) {
				throw new RuntimeException('Not a valid engine: ' . $engine);
			}
			$engines[] = $engine;
		}

		return $engines;
	}

}
