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

use Closure;
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Reflection\ClassReflection;
use ProxyManager\Configuration;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use ProxyManager\Proxy\ValueHolderInterface;
use ProxyManager\ProxyGenerator\ProxyGeneratorInterface;
use ProxyManager\Version;
use W7\Aspect\ProxyManager\Generator\LazyLoadingValueHolderGenerator;

class ProxyFactory extends LazyLoadingValueHolderFactory {
	protected array $checkedClasses = [];
	protected \ProxyManager\ProxyGenerator\LazyLoadingValueHolderGenerator $generator;

	public function __construct(?Configuration $configuration = null) {
		$this->configuration = $configuration ?? new Configuration();
		$this->generator = new LazyLoadingValueHolderGenerator();
	}

	/**
	 * @param array<string, mixed> $proxyOptions
	 *
	 * @psalm-template RealObjectType of object
	 *
	 * @psalm-param class-string<RealObjectType> $className
	 * @psalm-param Closure(
	 *   RealObjectType|null=,
	 *   RealObjectType&ValueHolderInterface<RealObjectType>&VirtualProxyInterface=,
	 *   string=,
	 *   array<string, mixed>=,
	 *   ?Closure=
	 * ) : bool $initializer
	 *
	 * @psalm-return RealObjectType&ValueHolderInterface<RealObjectType>
	 *
	 * @psalm-suppress MixedInferredReturnType We ignore type checks here, since `staticProxyConstructor` is not
	 *                                         interfaced (by design)
	 */
	public function createDelegationProxy(string $className, array $proxyOptions = []) {
		return $this->generateProxy($className, $proxyOptions);
	}

	protected function generateProxy(string $className, array $proxyOptions = []): string {
		if (array_key_exists($className, $this->checkedClasses)) {
			$generatedClassName = $this->checkedClasses[$className];

			assert(is_a($generatedClassName, $className, true));

			return $generatedClassName;
		}

		$proxyParameters = [
			'className'           => $className,
			'factory'             => static::class,
			'proxyManagerVersion' => Version::getVersion(),
			'proxyOptions'        => $proxyOptions,
		];
		$proxyClassName  = $this
			->configuration
			->getClassNameInflector()
			->getProxyClassName($className, $proxyParameters);
		$proxyParameters['proxyClassName'] = $proxyClassName;

		if (! class_exists($proxyClassName)) {
			$this->generateProxyClass(
				$proxyClassName,
				$className,
				$proxyParameters,
				$proxyOptions
			);
		}

		return $this->checkedClasses[$className] = $proxyClassName;
	}

	/**
	 * Generates the provided `$proxyClassName` from the given `$className` and `$proxyParameters`
	 *
	 * @param array<string, mixed> $proxyParameters
	 * @param array<string, mixed> $proxyOptions
	 *
	 * @psalm-param class-string $proxyClassName
	 * @psalm-param class-string $className
	 */
	protected function generateProxyClass(
		string $proxyClassName,
		string $className,
		array $proxyParameters,
		array $proxyOptions = []
	): void {
		$className = $this->configuration->getClassNameInflector()->getUserClassName($className);
		$reflectClass = new ClassReflection($className);
		$phpClass  = ClassGenerator::fromReflection($reflectClass);
		/** @psalm-suppress TooManyArguments - generator interface was not updated due to BC compliance */
		$this->getGenerator()->generate($reflectClass, $phpClass, $proxyOptions);

		/** @psalm-suppress TooManyArguments - generator interface was not updated due to BC compliance */
		$this->configuration->getGeneratorStrategy()->generate($phpClass, $proxyParameters);
	}

	protected function getGenerator(): ProxyGeneratorInterface {
		return $this->generator;
	}
}
