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

namespace W7\Aspect;

use W7\Aspect\Aop\AspectCollector;
use W7\Aspect\ProxyManager\ProxyClassCollector;
use W7\Core\Provider\ProviderAbstract;

class ServiceProvider extends ProviderAbstract {
	public function register() {
		$this->registerCommand();
		$this->initClassMethodAspect();
		$this->initProxyClassCollector();
	}

	protected function initClassMethodAspect() {
		$map = $this->config->get('aspect.class_method_aspects', []);
		foreach ($map as $originClassName => $aspectMap) {
			foreach ($aspectMap as $method => $aspect) {
				AspectCollector::addAspect($originClassName, $method, $aspect);
			}
		}
	}

	protected function initProxyClassCollector() {
		$map = $this->config->get('aspect.proxy_class', []);
		foreach ($map as $originClassName => $proxyClassName) {
			ProxyClassCollector::addClassProxy($originClassName, $proxyClassName);
		}
	}
}
