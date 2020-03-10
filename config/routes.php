<?php
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\Routing\Route\DashedRoute;

Router::prefix('Admin', function (RouteBuilder $routes) {
		$routes->plugin('Translate', ['path' => '/translate'], function (RouteBuilder $routes) {
			$routes->connect('/', ['controller' => 'Translate', 'action' => 'index'], ['routeClass' => DashedRoute::class]);

			$routes->fallbacks(DashedRoute::class);
		});
});
