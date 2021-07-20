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

namespace W7\Aspect\ProxyManager;

trait ProxyTrait {
	protected static function __proxyCall(
		string $originalClassName,
		string $method,
		array $arguments,
		\Closure $closure
	) {
	}
}
