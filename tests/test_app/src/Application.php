<?php

namespace TestApp;

use Cake\Core\PluginCollection;
use Cake\Http\BaseApplication;
use Cake\Http\MiddlewareQueue;
use Cake\Routing\Middleware\RoutingMiddleware;
use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

class Application extends BaseApplication {

	/**
	 * @return void
	 */
	public function bootstrap(): void {
	}

	/**
	 * @param \Cake\Routing\RouteBuilder $routes
	 *
	 * @return void
	 */
	public function routes(RouteBuilder $routes): void {
		$routes->prefix('Admin', function (RouteBuilder $routes) {
			$routes->plugin('Translate', ['path' => '/translate'], function (RouteBuilder $routes) {
				$routes->connect('/', ['controller' => 'Translate', 'action' => 'index'], ['routeClass' => DashedRoute::class]);

				$routes->fallbacks(DashedRoute::class);
			});
		});
	}

	/**
	 * @param \Cake\Http\MiddlewareQueue $middlewareQueue The middleware queue to set in your App Class
	 * @return \Cake\Http\MiddlewareQueue
	 */
	public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue {
		$middlewareQueue->add(new RoutingMiddleware($this));

		return $middlewareQueue;
	}

	/**
	 * Get the plugin collection in use.
	 *
	 * @return \Cake\Core\PluginCollection
	 */
	public function getPlugins(): PluginCollection {
		$this->addPlugin('Tools');

		return $this->plugins;
	}

}
