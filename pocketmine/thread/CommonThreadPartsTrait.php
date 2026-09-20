<?php

/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.2 Release
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

declare(strict_types=1);

namespace pocketmine\thread;

use pocketmine\Server;

/**
 * Common parts shared between Thread and Worker, adapted from PocketMine-MP's
 * src/thread/CommonThreadPartsTrait.php for the pmmpthread extension.
 */
trait CommonThreadPartsTrait{

	/** @var \pmmp\thread\ThreadSafeArray|\ClassLoader[]|null */
	private ?\pmmp\thread\ThreadSafeArray $classLoaders = null;

	/** @var bool */
	protected $isKilled = false;

	/**
	 * @return \ClassLoader[]|null
	 */
	public function getClassLoaders() : ?array{
		return $this->classLoaders !== null ? (array) $this->classLoaders : null;
	}

	/**
	 * @param \ClassLoader[]|null $autoloaders
	 */
	public function setClassLoaders(?array $autoloaders = null) : void{
		if($autoloaders === null){
			$autoloaders = [Server::getInstance()->getLoader()];
		}
		if($this->classLoaders === null){
			$this->classLoaders = new \pmmp\thread\ThreadSafeArray();
		}else{
			foreach($this->classLoaders as $k => $autoloader){
				unset($this->classLoaders[$k]);
			}
		}
		foreach($autoloaders as $autoloader){
			$this->classLoaders[] = $autoloader;
		}
	}

	/**
	 * Registers the class loaders for this thread.
	 *
	 * WARNING: This method MUST be called from any descendent threads' run() method to make autoloading usable.
	 * If you do not do this, you will not be able to use new classes that were not loaded when the thread was started
	 * (unless you are using a custom autoloader).
	 */
	public function registerClassLoaders() : void{
		if(!interface_exists("ClassLoader", false)){
			require(\pocketmine\PATH . "src/spl/ClassLoader.php");
			require(\pocketmine\PATH . "src/spl/BaseClassLoader.php");
			require(\pocketmine\PATH . "src/pocketmine/CompatibleClassLoader.php");
		}
		$autoloaders = $this->classLoaders;
		if($autoloaders !== null){
			foreach($autoloaders as $autoloader){
				/** @var \ClassLoader $autoloader */
				$autoloader->register(true);
			}
		}
	}

	/**
	 * Registers the class loader for this thread (legacy single-loader API kept for compatibility).
	 */
	public function registerClassLoader() : void{
		$this->registerClassLoaders();
	}

	/**
	 * Runs code on the thread.
	 */
	abstract protected function onRun() : void;

	/**
	 * Bridges pmmp\thread\Runnable::run() to the onRun() contract used throughout the codebase.
	 * Descendants should override onRun(), not run().
	 */
	public function run() : void{
		$this->registerClassLoaders();
		$this->onRun();
	}

	public function getThreadName() : string{
		return (new \ReflectionClass($this))->getShortName();
	}
}
