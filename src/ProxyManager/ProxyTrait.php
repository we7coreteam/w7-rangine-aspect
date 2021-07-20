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

use Illuminate\Pipeline\Pipeline;
use W7\App;
use W7\Aspect\Aop\AspectCollector;
use W7\Aspect\Aop\AspectJoinPoint;

trait ProxyTrait {
	protected static function __proxyCall(
		string $originalClassName,
		string $method,
		array $arguments,
		\Closure $closure
	) {
		if ($aspects = self::getAspects($originalClassName, $method)) {
			$aspectJoinPoint = new AspectJoinPoint($closure, $originalClassName, $method, $arguments);
			$pipeline = new Pipeline(App::getApp()->getContainer());
			return $pipeline->via('process')
				->through($aspects)
				->send($aspectJoinPoint)
				->then(function (AspectJoinPoint $aspectJoinPoint) {
					return $aspectJoinPoint->process();
				});
		}

		return $closure();
	}

	private static function getAspects($class, $method): array {
		return AspectCollector::getAspect($class, $method);
	}
}
