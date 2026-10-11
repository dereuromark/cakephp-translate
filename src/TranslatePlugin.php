<?php

namespace Translate;

use Cake\Console\CommandCollection;
use Cake\Container\ContainerInterface;
use Cake\Container\ReflectionContainer;
use Cake\Core\BasePlugin;
use Cake\Core\Configure;
use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;
use Translate\Command\I18nDumpCommand;
use Translate\Command\I18nExtractCommand;
use Translate\Command\I18nValidateCommand;
use Translate\Service\ExtractService;

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
		$routes->plugin('Translate', params: ['path' => '/translate'], callback: function (RouteBuilder $routes) {
			$routes->connect('/', ['controller' => 'Translate', 'action' => 'index'], ['routeClass' => DashedRoute::class]);

			$routes->fallbacks(DashedRoute::class);
		});

		$routes->prefix('Admin', function (RouteBuilder $routes) {
			$routes->plugin('Translate', params: ['path' => '/translate'], callback: function (RouteBuilder $routes) {
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
		//$commands->add('translations import', TranslationsCommand::class);
		$commands->add('i18n extract_to_db', I18nExtractCommand::class);
		$commands->add('i18n dump_from_db', I18nDumpCommand::class);
		$commands->add('i18n validate', I18nValidateCommand::class);

		return $commands;
	}

	/**
	 * @param \Cake\Container\ContainerInterface $container The container to add services to.
	 * @return void
	 */
	public function services(ContainerInterface $container): void {
		$container->add(ExtractService::class);
		$container->delegate(
			new ReflectionContainer(Configure::read('debug')),
		);
	}

}
