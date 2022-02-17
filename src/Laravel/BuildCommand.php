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

namespace W7\Aspect\Laravel;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use W7\Aspect\Aop\AspectAbstract;
use W7\Aspect\ProxyConfigTrait;
use W7\Aspect\ProxyManager\ProxyFactory;

class BuildCommand extends Command {
	use ProxyConfigTrait;

	protected $name = 'aspect:make';

	protected function execute(InputInterface $input, OutputInterface $output) {
		$scanDir = app_path('Aspect');
		$files = Finder::create()
			->in($scanDir)
			->files()
			->ignoreDotFiles(true)
			->name('/^[\w\W\d]+Aspect.php$/');

		$classNamespace = app()->getNamespace();
		$classMethodMap = [];
		$classProxyMap = [];
		$filesystem = new Filesystem();
		$filesystem->deleteDirectory($this->getProxyClassDir());
		$factory = new ProxyFactory($this->getConfiguration(true));

		/**
		 * @var SplFileInfo $file
		 */
		foreach ($files as $file) {
			$dir = trim(str_replace([$scanDir, DIRECTORY_SEPARATOR], ['', '\\'], $file->getPath()), '\\');
			/**
			 * @var AspectAbstract $aspectClass
			 */
			$aspectClass = $classNamespace . 'Aspect\\' . ($dir !== '' ? $dir . '\\' : '') . $file->getBasename('.php');
			foreach ($aspectClass::$classMethodMap as $class => $methods) {
				foreach ($methods as $method) {
					$classMethodMap[$class][$method][] = $aspectClass;
				}

				$proxyClass = $factory->createDelegationProxy(
					$class,
					['proxy_methods' => $methods]
				);
				$classProxyMap[$class] = $proxyClass;
			}
		}
		$data = [
			'class_method_aspects' => $classMethodMap,
			'proxy_class' => $classProxyMap
		];
		$filesystem->put(config_path('aspect.php'), '<?php' . "\n\rreturn " . var_export($data, true) . ';');

		$output->success('make aspect success');

		return 1;
	}
}
