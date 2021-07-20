<?php

namespace W7\Aspect;

use ProxyManager\Configuration;
use ProxyManager\FileLocator\FileLocator;
use ProxyManager\GeneratorStrategy\FileWriterGeneratorStrategy;
use W7\App;

trait ProxyConfigTrait {
	protected function getProxyClassDir() {
		return App::getApp()->getRuntimePath() . '/proxy/';
	}

	protected function getConfiguration($withGeneratorConfig = false) {
		$configuration = new Configuration();
		$proxyClassDir = $this->getProxyClassDir();
		if (!is_dir($proxyClassDir)) {
			isafeMakeDir($proxyClassDir, 0777, true);
		}
		$configuration->setProxiesTargetDir($proxyClassDir);
		$withGeneratorConfig && $configuration->setGeneratorStrategy(new FileWriterGeneratorStrategy(new FileLocator($proxyClassDir)));

		return $configuration;
	}
}