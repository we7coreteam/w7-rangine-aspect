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

namespace W7\Aspect\Aop;

class AspectCollector {
	protected static $rules = [];

	protected static function getAspectKey($class, $method) {
		return md5($class . $method);
	}

	public static function addAspect($class, $method, $aspectClass) {
		$aspectClass = (array)$aspectClass;
		$aspectKey = self::getAspectKey($class, $method);
		self::$rules[$aspectKey] = array_merge(self::$rules[$aspectKey] ?? [], $aspectClass);
	}

	public static function getAspect($class, $method) {
		return self::$rules[self::getAspectKey($class, $method)] ?? [];
	}
}
