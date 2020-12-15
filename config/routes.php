<?php

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;

Router::prefix('Admin', function (RouteBuilder $routes) {
		$routes->plugin('Translate', ['path' => '/translate'], function (RouteBuilder $routes) {
			$routes->connect('/', ['controller' => 'Translate', 'action' => 'index'], ['routeClass' => DashedRoute::class]);

			$routes->fallbacks(DashedRoute::class);
		});
});
