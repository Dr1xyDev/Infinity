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

use function spl_object_id;

class ThreadManager{

	/** @var ThreadManager|null */
	private static $instance = null;

	/** @var \pmmp\thread\ThreadSafeArray */
	private $threads;

	private function __construct(){
		//pmmp\thread\ThreadSafeArray is final, so it's held by composition instead of extended
		$this->threads = new \pmmp\thread\ThreadSafeArray();
	}

	public static function init() : void{
		if(self::$instance === null){
			self::$instance = new ThreadManager();
		}
	}

	public static function getInstance() : ThreadManager{
		if(self::$instance === null){
			self::$instance = new ThreadManager();
		}
		return self::$instance;
	}

	/**
	 * @param Worker|Thread $thread
	 */
	public function add($thread) : void{
		if($thread instanceof Thread or $thread instanceof Worker){
			$this->threads[spl_object_id($thread)] = $thread;
		}
	}

	/**
	 * @param Worker|Thread $thread
	 */
	public function remove($thread) : void{
		if($thread instanceof Thread or $thread instanceof Worker){
			unset($this->threads[spl_object_id($thread)]);
		}
	}

	/**
	 * @return Worker[]|Thread[]
	 */
	public function getAll() : array{
		$array = [];
		foreach($this->threads as $key => $thread){
			$array[$key] = $thread;
		}

		return $array;
	}

	public function stopAll() : int{
		$erroredThreads = 0;
		foreach($this->getAll() as $thread){
			try{
				$thread->quit();
			}catch(\Throwable $e){
				++$erroredThreads;
			}
		}
		return $erroredThreads;
	}
}
