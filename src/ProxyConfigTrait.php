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

use ProxyManager\Configuration;
use ProxyManager\FileLocator\FileLocator;
use ProxyManager\GeneratorStrategy\FileWriterGeneratorStrategy;

trait ProxyConfigTrait {
	private function getProxyClassDir() {
		if (defined('RUNTIME_PATH')) {
			$runtimePath = RUNTIME_PATH;
		} else {
			$runtimePath = dirname(__DIR__, 4) . '/runtime';
		}
		return $runtimePath . '/proxy/';
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
