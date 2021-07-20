<?php

namespace W7\Aspect;

use W7\Aspect\ProxyManager\ProxyClassCollector;
use W7\Core\Provider\ProviderAbstract;

class ServiceProvider extends ProviderAbstract {
	public function register() {
		$this->registerCommand();
		$this->initProxyClassCollector();
	}

	protected function initProxyClassCollector() {
		$map = $this->config->get('aspect.map', []);
		foreach ($map as $originClassName => $proxyClassName) {
			ProxyClassCollector::addClassProxy($originClassName, $proxyClassName);
		}
	}
}