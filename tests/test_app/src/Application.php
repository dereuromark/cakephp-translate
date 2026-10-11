<?php

namespace TestApp;

use Cake\Console\CommandCollection;
use Cake\Container\ContainerInterface;
use Cake\Container\ReflectionContainer;
use Cake\Core\Configure;
use Cake\Core\PluginCollection;
use Cake\Http\BaseApplication;
use Cake\Http\MiddlewareQueue;
use Cake\Routing\Middleware\RoutingMiddleware;
use Cake\Routing\RouteBuilder;
use Translate\TranslatePlugin;

class Application extends BaseApplication {

	/**
	 * @return void
	 */
	public function bootstrap(): void {
	}

	/**
	 * @param \Cake\Console\CommandCollection $commands
	 * @return \Cake\Console\CommandCollection
	 */
	public function console(CommandCollection $commands): CommandCollection {
		return (new TranslatePlugin())->console($commands);
	}

	/**
	 * @param \Cake\Container\ContainerInterface $container The container to add services to.
	 * @return void
	 */
	public function services(ContainerInterface $container): void {
		(new TranslatePlugin())->services($container);
		$container->delegate(
			new ReflectionContainer(Configure::read('debug')),
		);
	}

	/**
	 * @param \Cake\Routing\RouteBuilder $routes
	 *
	 * @return void
	 */
	public function routes(RouteBuilder $routes): void {
		(new TranslatePlugin())->routes($routes);
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
