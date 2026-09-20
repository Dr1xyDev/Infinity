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

/**
 * Specialized Worker class for Infinity-related use cases, based on pmmp\thread\Worker
 * (the maintained fork of pthreads). It handles setting up autoloading and error handling.
 */
abstract class Worker extends \pmmp\thread\Worker{
	use CommonThreadPartsTrait;

	public function start(int $options = \pmmp\thread\Thread::INHERIT_ALL) : bool{
		//this is intentionally not traitified
		ThreadManager::getInstance()->add($this);

		if(!$this->isStarted() and !$this->isJoined()){
			if($this->getClassLoaders() === null){
				$this->setClassLoaders();
			}
			return parent::start($options);
		}

		return false;
	}

	/**
	 * Stops the worker using the best way possible. Try to stop it yourself before calling this.
	 */
	public function quit() : void{
		$this->isKilled = true;

		if(!$this->isJoined()){
			$this->notify();
			$this->join();
		}

		ThreadManager::getInstance()->remove($this);
	}
}
