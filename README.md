## 安装

```
composer require w7/rangine-aspect
```

## 在rangine中使用

直接安装即可

## 在laravel中使用

直接安装即可。



### 编写Aspect

首先让我们编写待切入类

```php
<?php

namespace W7\App\Services;

class TestAspectService {
	public function test($arg) {
		return $arg;
	}

	public function test1() {
		return 1;
	}

	public function test2() {
		return 2;
	}
}
```

其次新增对应的 `TestAspect`

```php
<?php

namespace W7\App\Aspect;

use W7\App\Services\TestAspectService;
use W7\Aspect\Aop\AspectAbstract;
use W7\Aspect\Aop\AspectJoinPoint;

class TestAspect extends AspectAbstract {
    //表示切入到TestAspectService类的test和test1方法
	public static $classMethodMap = [
		TestAspectService::class => [
			'test',
			'test1'
		]
	];

	public function process(AspectJoinPoint $aspectJoinPoint, \Closure $next) {
		var_dump('aspect before ' . $aspectJoinPoint->class . ':' . $aspectJoinPoint->method);

		$result = $next($aspectJoinPoint);

		var_dump('aspect after ' . $aspectJoinPoint->class . ':' . $aspectJoinPoint->method);

		return $result;
	}
}
```

### 生成代理类
rangine
```
bin/gerent aspect:make
```
laravel
```
php artisan aspect:make
```

使用
```php
(new TestAspectService())->test('woshishui');
var_dump((new TestAspectService())->test1());
var_dump((new TestAspectService())->test2());
```
