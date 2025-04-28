<?php

namespace Translate;

use Cake\Console\CommandCollection;
use Cake\Core\BasePlugin;
use Cake\Core\Configure;
use Cake\Core\ContainerInterface;
use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;
use League\Container\ReflectionContainer;

/**
 * Plugin for Translate
 */
class TranslatePlugin extends BasePlugin {

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
	 * @param \Cake\Console\CommandCollection $commands
	 *
	 * @return \Cake\Console\CommandCollection
	 */
	public function console(CommandCollection $commands): CommandCollection {
		$commands = parent::console($commands);
		//$commands->add('translations import', TranslationsCommand::class);

		return $commands;
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

}
