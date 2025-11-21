<?php

namespace TestApp;

use Cake\Console\CommandCollection;
use Cake\Core\Configure;
use Cake\Core\ContainerInterface;
use Cake\Core\PluginCollection;
use Cake\Http\BaseApplication;
use Cake\Http\MiddlewareQueue;
use Cake\Routing\Middleware\RoutingMiddleware;
use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;
use League\Container\ReflectionContainer;
use Translate\Command\I18nDumpCommand;
use Translate\Command\I18nExtractCommand;
use Translate\Command\I18nValidateCommand;

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
		return $commands
			->add('i18n extract_to_db', new I18nExtractCommand())
			->add('i18n dump_from_db', new I18nDumpCommand())
			->add('i18n validate', new I18nValidateCommand());
	}

	/**
	 * @param \Cake\Core\ContainerInterface $container The container to add services to.
	 * @return void
	 */
	public function services(ContainerInterface $container): void {
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
