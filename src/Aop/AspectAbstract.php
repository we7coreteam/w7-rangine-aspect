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

abstract class AspectAbstract {
	/**
	 * The classes that you want to weaving.
	 *
	 * @var array
	 */
	public static $classMethodMap = [];

	abstract public function process(AspectJoinPoint $aspectJoinPoint, \Closure $next);
}
