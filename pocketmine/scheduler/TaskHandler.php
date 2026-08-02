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

use pocketmine\event\Timings;

class TaskHandler{

	
	protected $task;

	
	protected $taskId;

	
	protected $delay;

	
	protected $period;

	
	protected $nextRun;

	
	protected $cancelled = false;

	
	public $timings;

	public $timingName = null;

	
	public function __construct($timingName, Task $task, $taskId, $delay = -1, $period = -1){
		$this->task = $task;
		$this->taskId = $taskId;
		$this->delay = $delay;
		$this->period = $period;
		$this->timingName = $timingName === null ? "Unknown" : $timingName;
		$this->timings = Timings::getPluginTaskTimings($this, $period);
		$this->task->setHandler($this);
	}

	
	public function isCancelled(){
		return $this->cancelled === true;
	}

	
	public function getNextRun(){
		return $this->nextRun;
	}

	
	public function setNextRun($ticks){
		$this->nextRun = $ticks;
	}

	
	public function getTaskId(){
		return $this->taskId;
	}

	
	public function getTask(){
		return $this->task;
	}

	
	public function getDelay(){
		return $this->delay;
	}

	
	public function isDelayed(){
		return $this->delay > 0;
	}

	
	public function isRepeating(){
		return $this->period > 0;
	}

	
	public function getPeriod(){
		return $this->period;
	}

	
	public function cancel(){
		if(!$this->isCancelled()){
			$this->task->onCancel();
		}
		$this->remove();
	}

	public function remove(){
		$this->cancelled = true;
		$this->task->setHandler(null);
	}

	
	public function run($currentTick){
		$this->task->onRun($currentTick);
	}

	
	public function getTaskName(){
		if($this->timingName !== null){
			return $this->timingName;
		}

		return get_class($this->task);
	}
}
