<?php

namespace W7\Aspect\ProxyManager\Autoloader;

use W7\Aspect\ProxyManager\ProxyClassCollector;

class Autoloader extends \ProxyManager\Autoloader\Autoloader {
	public function __invoke(string $className): bool {
		if ($targetClassName = ProxyClassCollector::getProxyClass($className)) {
			$file = $this->fileLocator->getProxyFileName($targetClassName);
			if (file_exists($file)) {
				return (bool) require_once $file;
			}
		}

		return false;
	}
}
