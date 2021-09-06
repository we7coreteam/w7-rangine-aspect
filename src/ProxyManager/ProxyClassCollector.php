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

class ProxyClassCollector {
	protected static $classMap = [];

	public static function addClassProxy($originClassName, $proxyClassName) {
		$originClassName = ltrim($originClassName, '\\');
		self::$classMap[$originClassName] = $proxyClassName;
	}

	public static function getProxyClass($originClassName) {
		$tmpOriginClassName = ltrim($originClassName, '\\');
		return self::$classMap[$tmpOriginClassName] ?? '';
	}
}
