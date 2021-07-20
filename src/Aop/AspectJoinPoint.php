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

use Closure;

class AspectJoinPoint {
	/**
	 * @var string
	 */
	public $class;

	/**
	 * @var string
	 */
	public $method;

	/**
	 * @var mixed[]
	 */
	public $arguments;

	/**
	 * @var Closure
	 */
	public $originMethodClosure;

	public function __construct(Closure $originalMethod, string $class, string $method, array $arguments = []) {
		$this->originMethodClosure = $originalMethod;
		$this->class = $class;
		$this->method = $method;
		$this->arguments = $arguments;
	}

	/**
	 * Delegate to the next aspect.
	 */
	public function process() {
		$closure = $this->originMethodClosure;
		return $closure();
	}
}
