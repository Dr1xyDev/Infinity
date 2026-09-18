<?php

/*
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev
 *   YT:         @Dr1xyDev
 *   GitHub:     github.com/Dr1xyDev/Infinity
*/

declare(strict_types=1);

namespace pocketmine\thread;

/**
 * Specialized Thread class aimed at Infinity-related usages, based on pmmp\thread\Thread
 * (the maintained fork of pthreads). It handles setting up autoloading and error handling.
 */
abstract class Thread extends \pmmp\thread\Thread{
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
	 * Stops the thread using the best way possible. Try to stop it yourself before calling this.
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
