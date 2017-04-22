<?php
/**
 */

namespace Translate\Translator;


use Cake\Core\Configure;
use Cake\Core\InstanceConfigTrait;
use RuntimeException;
use Translate\Translator\Engine\Google;

class Translator
{

	use InstanceConfigTrait;

	protected $_defaultConfig = [
		'engine' => Google::class,
	];

	public function __construct(array $config = [])
	{
		$config += (array)Configure::read('Translate');
		$this->setConfig($config);

		$engine = $this->getConfig('engine');
	}

	/**
	 * @param string $text Text up to 5000 chars
	 * @param string $to Iso2 code (e.g.: de)
	 * @param string $from Iso2 code (e.g.: en)
	 *
	 * @return string|null
	 */
	public function translate($text, $to, $from) {
		$engine = $this->_getEngine();

		return $engine->translate($text, $to, $from);
	}

	/**
	 * @return \Translate\Translator\EngineInterface
	 */
	protected function _getEngine()
	{
		$engineClass = $this->getConfig('engine');
		$engine = new $engineClass($this->getConfig());
		if (!($engine instanceof EngineInterface)) {
			throw new RuntimeException('Not a valid engine: ' . $engine);
		}

		return $engine;
	}

}
