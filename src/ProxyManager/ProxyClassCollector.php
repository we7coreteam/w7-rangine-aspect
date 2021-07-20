<?php

namespace W7\Aspect\ProxyManager;

class ProxyClassCollector {
	protected static $classMap = [];

	public static function addClassProxy($originClassName, $proxyClassMap) {
		$originClassName = ltrim($originClassName, '\\');
		self::$classMap[$originClassName] = $proxyClassMap;
	}

	public static function getProxyClass($originClassName) {
		$tmpOriginClassName = ltrim($originClassName, '\\');
		return self::$classMap[$tmpOriginClassName] ?? $originClassName;
	}
}