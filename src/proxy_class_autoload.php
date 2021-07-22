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

use W7\Aspect\ProxyClassLoader;
use W7\Aspect\ServiceProvider;
use W7\Core\Bootstrap\ProviderBootstrap;

$proxyClassLoader = new ProxyClassLoader();
spl_autoload_register([$proxyClassLoader, 'loadClass']);

array_splice(ProviderBootstrap::$providerMap, 1, 0, ['aspect' => ServiceProvider::class]);
