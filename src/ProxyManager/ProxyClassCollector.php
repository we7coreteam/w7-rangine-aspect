<?php

namespace W7\Aspect\ProxyManager;

class ProxyClassCollector {
	protected static $classMap = [];

	public static function addClassProxy($originClassName, $proxyClassPath) {
		$originClassName = ltrim($originClassName, '\\');
		self::$classMap[$originClassName] = $proxyClassPath;
	}

	public static function getProxyClass($originClassName) {
		$tmpOriginClassName = ltrim($originClassName, '\\');
		return self::$classMap[$tmpOriginClassName] ?? '';
	}
}