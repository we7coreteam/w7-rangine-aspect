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

namespace W7\Aspect\ProxyManager\Generator;

use InvalidArgumentException;
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Reflection\ClassReflection;
use Laminas\Code\Reflection\MethodReflection;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\Node\Stmt\TraitUseAdaptation\Alias;
use PhpParser\Node\Stmt\TraitUseAdaptation\Precedence;
use PhpParser\ParserFactory;
use ProxyManager\Exception\InvalidProxiedClassException;
use ProxyManager\Generator\Util\ProxiedMethodReturnExpression;
use ProxyManager\ProxyGenerator\Assertion\CanProxyAssertion;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\LazyLoadingMethodInterceptor;
use ReflectionClass;
use ReflectionMethod;

use function array_map;

/**
 * Generator for proxies implementing {@see \ProxyManager\Proxy\VirtualProxyInterface}
 *
 * {@inheritDoc}
 */
class LazyLoadingValueHolderGenerator extends \ProxyManager\ProxyGenerator\LazyLoadingValueHolderGenerator {
	protected $defaultTrait = ['\W7\Aspect\ProxyManager\ProxyTrait'];

	protected function getComposerLoader() {
		if (defined('BASE_PATH')) {
			$vendorPath = BASE_PATH . '/vendor/';
		} else {
			$vendorPath = dirname(__DIR__, 5) . '/';
		}

		return include $vendorPath . 'autoload.php';
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return void
	 *
	 * @throws InvalidProxiedClassException
	 * @throws InvalidArgumentException
	 */
	public function generate(ReflectionClass $originalClass, ClassGenerator $classGenerator, array $proxyOptions = []) {
		CanProxyAssertion::assertClassCanBeProxied($originalClass);

		//method
		/**
		 * @var ClassReflection $trait
		 */
		$traitMethods = [];
		foreach ($originalClass->getTraits() as $trait) {
			foreach ($trait->getMethods() as $method) {
				$traitMethods[] = $method->getName();
			}
		}
		$proxyMethods = ProxiedMethodsFilter::getProxiedMethods($originalClass, $proxyOptions['proxy_methods'] ?? []);
		foreach ($proxyMethods as $method) {
			$classGenerator->removeMethod($method->getName());
		}
		foreach ($originalClass->getMethods() as $method) {
			if (
				$method->getDeclaringClass()->getName() !== $originalClass->getName() ||
				in_array($method->getName(), $traitMethods)
			) {
				$classGenerator->hasMethod($method->getName()) && $classGenerator->removeMethod($method->getName());
			}
		}
		foreach ($originalClass->getTraitAliases() as $alias => $origin) {
			if ($originalClass->getMethod($alias)->getFileName() != $originalClass->getFileName()) {
				$classGenerator->removeMethod($alias);
			}
		}

		//use
		$composerLoader = $this->getComposerLoader();
		$file = $composerLoader->findFile($originalClass->getName());
		$parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
		$stmts = $parser->parse(file_get_contents($file));
		/**
		 * @var \PhpParser\Node\Stmt\Namespace_ $stmt
		 */
		foreach ($stmts  as $stmt) {
			foreach ($stmt->stmts as $_stmt) {
				if ($_stmt instanceof \PhpParser\Node\Stmt\Use_) {
					foreach ($_stmt->uses as $use) {
						$alias = null;
						if ($use->name->getLast() != $use->getAlias()->name) {
							$alias = $use->getAlias()->name;
						}
						$classGenerator->addUse($use->name->toCodeString(), $alias);
					}
				}
				if ($_stmt instanceof Class_) {
					foreach ($_stmt->stmts as $__stmt) {
						if ($__stmt instanceof TraitUse) {
							foreach ($__stmt->traits as $trait) {
								$classGenerator->addTrait($trait->toCodeString());
							}
							foreach ($__stmt->adaptations as $adaptation) {
								if ($adaptation instanceof Precedence) {
									$insteadOfs = [];
									foreach ($adaptation->insteadof as $item) {
										$insteadOfs[] = $item->toString();
									}
									$classGenerator->addTraitOverride([
										'traitName' => $adaptation->trait->toCodeString(),
										'method' => $adaptation->method->toString()
									], $insteadOfs);
								}
								if ($adaptation instanceof Alias) {
									$classGenerator->addTraitAlias([
										'traitName' => $adaptation->trait->toCodeString(),
										'method' => $adaptation->method->toString()
									], $adaptation->newName->toString(), $adaptation->newModifier);
								}
							}
						}
					}
				}
			}
		}
		$traits = array_merge($this->defaultTrait, (array)($proxyOptions['proxy_traits'] ?? []));
		foreach ($traits as $item) {
			$classGenerator->addTrait($item);
		}

		// generate method
		array_map(
			static function (MethodGenerator $generatedMethod) use ($originalClass, $classGenerator): void {
				$classGenerator->addMethodFromGenerator($generatedMethod);
			},
			array_map(
				$this->buildMethodInterceptor($originalClass, $proxyOptions['proxy_methods'] ?? []),
				$proxyMethods
			)
		);
	}

	private function buildMethodInterceptor(ReflectionClass $originalClass, array $proxyMethods = []): callable {
		return static function (ReflectionMethod $method) use ($originalClass, $proxyMethods) : MethodGenerator {
			return self::generateMethod(
				$originalClass,
				new MethodReflection($method->getDeclaringClass()->getName(), $method->getName()),
				$proxyMethods
			);
		};
	}

	private static function generateMethod(
		ReflectionClass $originalClass,
		MethodReflection $originalMethod,
		array $proxyMethods = []
	): MethodGenerator {
		$method = LazyLoadingMethodInterceptor::fromReflection($originalMethod);
		if (!in_array($originalMethod->getName(), $proxyMethods)) {
			return $method;
		}

		$parameters        = $originalMethod->getParameters();
		$methodName        = $originalMethod->getName();
		$forwardedParams   = [];
		$initializerParams = [];

		foreach ($parameters as $parameter) {
			$parameterName       = $parameter->getName();
			$variadicPrefix      = $parameter->isVariadic() ? '...' : '';
			$forwardedParams[]   = $variadicPrefix . '$' . $parameterName;
			$initializerParams[] = var_export($parameterName, true) . ' => $' . $parameterName;
		}

		$inlineFunction = 'function()';
		if ($forwardedParams) {
			$inlineFunction .= ' use (' . implode(', ', $forwardedParams) . ')';
		}

		$methodBody = 'self::__proxyCall(\\' . $originalClass->getName() . '::class, ' . var_export($methodName, true) . ', array(' . implode(', ', $initializerParams) . '), ' . $inlineFunction . ' {'
			.
			$method->getBody() . "\n\r"
			. '        })';
		if (!$originalMethod->isConstructor() && !$originalMethod->isDestructor()) {
			$methodBody = ProxiedMethodReturnExpression::generate($methodBody, $originalMethod);
		} else {
			$methodBody .= ';';
		}
		$method->setBody($methodBody);

		return $method;
	}
}
