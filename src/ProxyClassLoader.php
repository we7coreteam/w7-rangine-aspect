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

use ProxyManager\Configuration;

class ProxyClassLoader {
	/**
	 * @varConfiguration
	 */
	protected $configuration;

	public function __construct($baseDir) {
		$this->configuration = new Configuration();
		$this->configuration->setProxiesTargetDir($baseDir);
	}

	public function loadClass($class) {
		$result = $this->configuration->getProxyAutoloader()($class);
		if ($result === false) {
			return false;
		}

		return true;
	}
}
