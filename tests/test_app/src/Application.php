<?php

namespace App;

use Cake\Core\PluginCollection;
use Cake\Http\BaseApplication;
use Cake\Http\MiddlewareQueue;
use Cake\Routing\Middleware\RoutingMiddleware;

class Application extends BaseApplication {

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
	public function getPlugins(): PluginCollection
	{
		$this->addPlugin('Tools');

		return $this->plugins;
	}

}
