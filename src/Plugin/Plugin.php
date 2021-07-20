<?php

namespace W7\Aspect\Plugin;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Util\Filesystem;
use Composer\Plugin\PluginInterface;
use Composer\EventDispatcher\EventSubscriberInterface;
use W7\Core\Container\Container;

class Plugin implements PluginInterface, EventSubscriberInterface {
	/**
	 * @var \Composer\Composer
	 */
	protected $composer;

	/**
	 * @var \Composer\IO\IOInterface
	 */
	protected $io;

	/**
	 * Apply plugin modifications to Composer
	 *
	 * @param Composer    $composer
	 * @param IOInterface $io
	 */
	public function activate(Composer $composer, IOInterface $io) {
		$this->composer = $composer;
		$this->io = $io;
	}

	public function deactivate(Composer $composer, IOInterface $io) {

    }

    public function uninstall(Composer $composer, IOInterface $io) {

    }

	/**
	 * @return array
	 */
	public static function getSubscribedEvents() {
		return array(
			'post-autoload-dump' => 'processFile',
		);
	}

	public static function process(\Composer\Script\Event $event) {
		$plugin = new static();
		$plugin->activate($event->getComposer(), $event->getIO());

		$plugin->processFile();
	}

	public function processFile() {
		$filePath = dirname(__DIR__) . '/Container/Container.php';
		$config = $this->composer->getConfig();

		$filesystem = new Filesystem();
		$filesystem->ensureDirectoryExists($config->get('vendor-dir'));
		$vendorPath = $filesystem->normalizePath(realpath(realpath($config->get('vendor-dir'))));

		$containerPath= $vendorPath . '/w7/rangine/Src/Core/Container/Container.php';
		include $containerPath;
		$refFunction = new \ReflectionMethod(new Container() ,'set');
		$content = file_get_contents($containerPath);
		$eol = $this->getEOL($content);
		$contents = explode($eol, $content);

		$replaces = [
			'		if (is_object($handle) && (!$handle instanceof \Closure)) {',
			'			$this->instance($name, $handle);',
			'		} else {',
			'			if (is_string($handle)) { $handle = \W7\Aspect\ProxyManager\ProxyClassCollector::getProxyClass($handle);} $this->bind($name, $handle, $shared);',
			'		}'
		];
		$replaceIndex = 0;
		for($i = $refFunction->getStartLine(); $i < $refFunction->getEndLine() - 1; ++$i,++$replaceIndex) {
			$contents[$i] = $replaces[$replaceIndex];
		}
		$content = implode($eol, $contents);
		file_put_contents($filePath, $content);
	}

	/**
	 * 获取换行符
	 *
	 * @param string $content
	 * @return string
	 */
	protected function getEOL($content) {
		static $eols = [
			"\r\n",
			"\n",
			"\r",
		];
		foreach($eols as $eol) {
			if(strpos($content, $eol)) {
				return $eol;
			}
		}
		return PHP_EOL;
	}
}