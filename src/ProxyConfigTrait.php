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
use W7\Aspect\ProxyManager\Autoloader\Autoloader;
use W7\Aspect\ProxyManager\GeneratorStrategy\FileWriterGeneratorStrategy;

trait ProxyConfigTrait {
	protected function getProxyClassDir() {
		if (defined('RUNTIME_PATH')) {
			$runtimePath = RUNTIME_PATH;
		} else {
			$runtimePath = dirname(__DIR__, 4) . '/runtime';
		}
		return $runtimePath . '/proxy/';
	}

	private static function safeMakeDir($dir, $permissions = 0777, $recursive = false) {
		if (!mkdir($dir, $permissions, $recursive) && !is_dir($dir)) {
			throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
		}
	}

	protected function getConfiguration($withGeneratorConfig = false) {
		$configuration = new Configuration();
		$proxyClassDir = $this->getProxyClassDir();
		if (!is_dir($proxyClassDir)) {
			self::safeMakeDir($proxyClassDir, 0777, true);
		}
		$configuration->setProxiesTargetDir($proxyClassDir);
		$withGeneratorConfig && $configuration->setGeneratorStrategy(new FileWriterGeneratorStrategy(new FileLocator($proxyClassDir)));
		$configuration->setProxyAutoloader(new Autoloader(
			new FileLocator($configuration->getProxiesTargetDir()),
			$configuration->getClassNameInflector()
		));

		return $configuration;
	}
}
