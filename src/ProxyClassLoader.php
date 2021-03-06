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

/**
 * Rangine Aspect
 *
 * (c) We7Team 2019 <https://www.w7.cc>
 *
 * This is not a free software
 * Using it under the license terms
 * visited https://www.w7.cc for more details
 */
class ProxyClassLoader {
	use ProxyConfigTrait;

	/**
	 * @var Configuration
	 */
	protected $configuration;

	public function __construct() {
		$this->configuration = $this->getConfiguration();
	}

	public function loadClass($class) {
		$result = $this->configuration->getProxyAutoloader()($class);
		if ($result === false) {
			return null;
		}

		return true;
	}
}
