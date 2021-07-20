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

namespace W7\Tests;

use PHPUnit\Framework\TestCase;
use ProxyManager\Configuration;
use ProxyManager\FileLocator\FileLocator;
use ProxyManager\GeneratorStrategy\FileWriterGeneratorStrategy;
use W7\App;
use W7\Aspect\ProxyManager\ProxyFactory;

class ClassProxyTest extends TestCase {
	public function testConvert() {
		$config = new Configuration();
		$config->setProxiesTargetDir(__DIR__);
		$config->setGeneratorStrategy(new FileWriterGeneratorStrategy(new FileLocator(__DIR__)));
		$factory = new ProxyFactory($config);

		$proxy = $factory->createDelegationProxy(
			App::class,
			['proxy_methods' => ['getContainer']]
		);

		$this->assertSame(true, class_exists($proxy));
	}
}
