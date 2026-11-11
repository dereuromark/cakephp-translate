<?php

namespace Translate\Test\TestCase;

use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Event\EventManager;
use Cake\Http\MiddlewareQueue;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Base Integration Test Case for controller tests
 */
abstract class IntegrationTestCase extends TestCase {

	use IntegrationTestTrait;

	/**
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		// The plugin's beforeFilter requires `Translate.adminAccess` to be a Closure
		// returning true for permitted callers. Tests run as a permitted caller by
		// default; individual tests can override the gate to assert the deny path.
		Configure::write('Translate.adminAccess', function ($request): bool {
			return true;
		});
	}

	/**
	 * @return void
	 */
	protected function tearDown(): void {
		Configure::delete('Translate.adminAccess');

		parent::tearDown();
	}

	/**
	 * Adds a middleware that sets request attributes, like a CSP middleware would.
	 *
	 * @param array<string, string> $attributes
	 *
	 * @return void
	 */
	protected function withRequestAttributes(array $attributes): void {
		EventManager::instance()->on('Server.buildMiddleware', function (EventInterface $event, MiddlewareQueue $queue) use ($attributes): void {
			$queue->prepend(function (ServerRequestInterface $request, RequestHandlerInterface $handler) use ($attributes): ResponseInterface {
				foreach ($attributes as $name => $value) {
					$request = $request->withAttribute($name, $value);
				}

				return $handler->handle($request);
			});
		});
	}

}
