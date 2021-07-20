<?php

namespace W7\Aspect\Command\Aspect;

use W7\Aspect\ProxyConfigTrait;
use W7\Aspect\ProxyManager\ProxyFactory;
use W7\Console\Command\CommandAbstract;
use W7\Core\Bootstrap\LoadConfigBootstrap;
use W7\Core\Controller\FaviconController;

class TestCommand extends CommandAbstract {
	use ProxyConfigTrait;

	protected function handle($options) {
		$factory = new ProxyFactory($this->getConfiguration(true));

		$proxyClass = $factory->createDelegationProxy(
			FaviconController::class,
			['proxy_methods' => ['index']]
		);

		$map = $this->getConfig()->get('aspect.map', []);
		$map[FaviconController::class] = $proxyClass;
		$data = [
			'map' => $map
		];

		file_put_contents((new LoadConfigBootstrap())->getBuiltInConfigPath() . '/aspect.php', '<?php' . "\n\rreturn " . var_export($data, true) . ';');
	}
}