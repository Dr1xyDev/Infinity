<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

namespace pocketmine\scheduler;

use pocketmine\Server;

abstract class AsyncTask extends \Threaded implements \Collectable{

	
	public $worker = null;

	private $result = null;
	private $serialized = false;
	private $cancelRun = false;
	
	private $taskId = null;

	private $crashed = false;

	private $isGarbage = false;

	private $isFinished = false;

	public function isGarbage() : bool{
		return $this->isGarbage;
	}

	public function setGarbage(){
		$this->isGarbage = true;
	}

	public function isFinished() : bool{
		return $this->isFinished;
	}

	public function run(){
		$this->result = null;
		$this->isGarbage = false;

		if($this->cancelRun !== true){
			try{
				$this->onRun();
			}catch(\Throwable $e){
				$this->crashed = true;
				$this->worker->handleException($e);
			}
		}

		$this->isFinished = true;
		
	}

	public function isCrashed(){
		return $this->crashed;
	}

	
	public function getResult(){
		return $this->serialized ? unserialize($this->result) : $this->result;
	}

	public function cancelRun(){
		$this->cancelRun = true;
	}

	public function hasCancelledRun(){
		return $this->cancelRun === true;
	}

	
	public function hasResult(){
		return $this->result !== null;
	}

	
	public function setResult($result, $serialize = true){
		$this->result = $serialize ? serialize($result) : $result;
		$this->serialized = $serialize;
	}

	public function setTaskId($taskId){
		$this->taskId = $taskId;
	}

	public function getTaskId(){
		return $this->taskId;
	}

	
	public function getFromThreadStore($identifier){
		global $store;
		return $this->isGarbage() ? null : $store[$identifier];
	}

	
	public function saveToThreadStore($identifier, $value){
		global $store;
		if(!$this->isGarbage()){
			$store[$identifier] = $value;
		}
	}

	
	public abstract function onRun();

	
	public function onCompletion(Server $server){

	}

	public function cleanObject(){
		foreach($this as $p => $v){
			if(!($v instanceof \Threaded) and !in_array($p, ["isFinished", "isGarbage", "cancelRun"])){
				$this->{$p} = null;
			}
		}
	}

}
