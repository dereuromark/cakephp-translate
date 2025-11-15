<?php

namespace Translate\Controller\Component;

use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Event\EventInterface;
use Cake\Http\Response;
use Cake\ORM\TableRegistry;

/**
 * Translation Component
 *
 * Provides translation utilities, project management, and common controller helpers.
 * Automatically trims whitespace from request data unless disabled via configuration.
 *
 * @method \App\Controller\AppController getController()
 */
class TranslationComponent extends Component {

	/**
	 * Startup callback - automatically trims request data
	 *
	 * @param \Cake\Event\EventInterface $event Event
	 * @return void
	 */
	public function startup(EventInterface $event): void {
		if ($this->getConfig('notrim') || Configure::read('DataPreparation.notrim')) {
			return;
		}

		$controller = $this->getController();
		$request = $controller->getRequest();

		if ($request->getData()) {
			$newData = $this->trimDeep($request->getData());
			foreach ($newData as $k => $v) {
				if ($request->getData($k) !== $v) {
					$request = $request->withData($k, $v);
				}
			}
		}
		if ($request->getQuery()) {
			$queryData = $this->trimDeep($request->getQuery());
			if ($queryData !== $request->getQuery()) {
				$request = $request->withQueryParams($queryData);
			}
		}
		if ($request->getParam('pass')) {
			$passData = $this->trimDeep($request->getParam('pass'));
			if ($passData !== $request->getParam('pass')) {
				$request = $request->withParam('pass', $passData);
			}
		}

		if ($request === $controller->getRequest()) {
			return;
		}

		$controller->setRequest($request);
	}

	/**
	 * @return int|null
	 */
	public function currentProjectId() {
		$id = $this->getController()->getRequest()->getSession()->read('TranslateProject.id');
		if ($id === null) {
			/** @var \Translate\Model\Table\TranslateProjectsTable $TranslationProjects */
			$TranslationProjects = TableRegistry::getTableLocator()->get('Translate.TranslateProjects');
			$id = $TranslationProjects->getDefaultProjectId();

			$this->getController()->getRequest()->getSession()->write('TranslateProject.id', $id);
		}

		return $id;
	}

	/**
	 * @return void
	 */
	public function hardReset(): void {
		/** @var \Cake\Database\Connection $connection */
		$connection = ConnectionManager::get('default');

		$tables = [
			'translate_domains',
			'translate_strings',
			'translate_locales',
			'translate_terms',
			'translate_api_translations',
		];

		$tableTruncates = 'TRUNCATE TABLE ' . implode(';' . PHP_EOL . 'TRUNCATE TABLE ', $tables) . ';';

		$sql = <<<SQL
SET FOREIGN_KEY_CHECKS = 0;

$tableTruncates

SET FOREIGN_KEY_CHECKS = 1;
SQL;
		$connection->execute($sql);
	}

	/**
	 * Check if the current request is a "POSTED" request
	 *
	 * @return bool
	 */
	public function isPosted(): bool {
		return $this->getController()->getRequest()->is(['post', 'put', 'patch']);
	}

	/**
	 * Auto-redirect helper that redirects to a URL or referrer
	 *
	 * @param array|string|null $url URL to redirect to, or null to use referer
	 * @return \Cake\Http\Response
	 */
	public function autoRedirect(array|string|null $url = null): Response {
		if ($url === null) {
			$url = (string)$this->getController()->getRequest()->referer(true);
			if ($url === '') {
				$url = '/';
			}
		}

		return $this->getController()->redirect($url);
	}

	/**
	 * Recursively trim whitespace from strings in arrays
	 *
	 * @param mixed $value Value to trim
	 * @return mixed Trimmed value
	 */
	protected function trimDeep(mixed $value): mixed {
		if (is_array($value)) {
			return array_map([$this, 'trimDeep'], $value);
		}
		if (is_string($value)) {
			return trim($value);
		}

		return $value;
	}

}
