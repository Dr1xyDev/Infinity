<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____  
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/ |  _ \ 
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/ 
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___/     |_|  |_|_| 
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

declare(strict_types=1);

namespace pocketmine\thread;

use pmmp\thread\Worker as PmmpWorker;

/**
 * This class must be extended by all custom threading classes
 */
abstract class Worker extends PmmpWorker{

	/** @var \ClassLoader|null */
	protected $classLoader;
	/** @var bool */
	protected $isKilled = false;

	public function getClassLoader(){
		return $this->classLoader;
	}

	/**
	 * @return void
	 */
	public function setClassLoader(?\ClassLoader $loader = null){
		if($loader === null){
			$server = \pocketmine\Server::getInstance();
			if($server === null){
				return;
			}
			$loader = $server->getLoader();
		}
		$this->classLoader = $loader;
	}

	/**
	 * @return void
	 */
	public function registerClassLoader(){
		if(!interface_exists("ClassLoader", false)){
			require(\pocketmine\PATH . "src/spl/ClassLoader.php");
			require(\pocketmine\PATH . "src/spl/BaseClassLoader.php");
			require(\pocketmine\PATH . "src/pocketmine/CompatibleClassLoader.php");
		}
		if($this->classLoader !== null){
			$this->classLoader->register(true);
		}
	}

	public function start(int $options = PmmpWorker::INHERIT_ALL) : bool{
		$manager = ThreadManager::getInstance();
		if($manager !== null){
			$manager->add($this);
		}

		if(!$this->isStarted() or (!$this->isRunning() and !$this->isJoined() and !$this->isTerminated())){
			if($this->getClassLoader() === null){
				$this->setClassLoader();
			}
			return parent::start($options);
		}

		return false;
	}

	/**
	 * Stops the thread using the best way possible. Try to stop it yourself before calling this.
	 */
	public function quit() : void{
		$this->isKilled = true;

		$this->notify();

		if($this->isRunning()){
			$this->shutdown();
			$this->notify();
			$this->unstack();
		}elseif(!$this->isJoined()){
			if(!$this->isTerminated()){
				$this->join();
			}
		}

		$manager = ThreadManager::getInstance();
		if($manager !== null){
			$manager->remove($this);
		}
	}

	/**
	 * @return string
	 */
	public function getThreadName(){
		return (new \ReflectionClass($this))->getShortName();
	}
}
