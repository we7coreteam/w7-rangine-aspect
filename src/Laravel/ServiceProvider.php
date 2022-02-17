<?php

/**
 * Rangine Aspect
 *
 * (c) We7Team 2019 <https://www.w7.cc>
 *
 * This is not a free software
 * Using it under the license terms
 * visited https://www.w7.cc for more details
 */

namespace W7\Aspect\Laravel;

use W7\Aspect\Aop\AspectCollector;
use W7\Aspect\ProxyManager\ProxyClassCollector;

class ServiceProvider extends \Illuminate\Support\ServiceProvider {
	public function register() {
		$this->initClassMethodAspect();
		$this->initProxyClassCollector();
		$this->app->runningInConsole() && $this->registerCommand();
	}

	protected function initClassMethodAspect() {
		$map = config()->get('aspect.class_method_aspects', []);
		foreach ($map as $originClassName => $aspectMap) {
			foreach ($aspectMap as $method => $aspect) {
				AspectCollector::addAspect($originClassName, $method, $aspect);
			}
		}
	}

	protected function initProxyClassCollector() {
		$map = config()->get('aspect.proxy_class', []);
		foreach ($map as $originClassName => $proxyClassName) {
			ProxyClassCollector::addClassProxy($originClassName, $proxyClassName);
		}
	}

	protected function registerCommand() {
		$this->app->singleton('command.aspect.make', function () {
			return new BuildCommand();
		});
		$this->commands(['command.aspect.make']);
	}
}
