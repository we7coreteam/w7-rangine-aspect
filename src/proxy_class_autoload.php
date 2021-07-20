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

$proxyClassLoader = new ProxyClassLoader();
spl_autoload_register([$proxyClassLoader, 'loadClass']);
