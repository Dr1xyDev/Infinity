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

use pocketmine\plugin\Plugin;
use pocketmine\Server;
use pocketmine\utils\MainLogger;
use pocketmine\utils\PluginException;
use pocketmine\utils\ReversePriorityQueue;

class ServerScheduler{
	public static $WORKERS = 2;
	
	protected $queue;

	
	protected $tasks = [];

	
	protected $asyncPool;

	
	private $ids = 1;

	
	protected $currentTick = 0;

	public function __construct(){
		$this->queue = new ReversePriorityQueue();
		$this->asyncPool = new AsyncPool(Server::getInstance(), self::$WORKERS);
	}

	
	public function scheduleTask(Task $task){
		return $this->addTask($task, -1, -1);
	}

	
	public function scheduleAsyncTask(AsyncTask $task){
		$id = $this->nextId();
		$task->setTaskId($id);
		$this->asyncPool->submitTask($task);
	}

	
	public function scheduleAsyncTaskToWorker(AsyncTask $task, $worker){
		$id = $this->nextId();
		$task->setTaskId($id);
		$this->asyncPool->submitTaskToWorker($task, $worker);
	}

	public function getAsyncTaskPoolSize(){
		return $this->asyncPool->getSize();
	}

	public function increaseAsyncTaskPoolSize($newSize){
		$this->asyncPool->increaseSize($newSize);
	}

	
	public function scheduleDelayedTask(Task $task, $delay){
		return $this->addTask($task, (int) $delay, -1);
	}

	
	public function scheduleRepeatingTask(Task $task, $period){
		return $this->addTask($task, -1, (int) $period);
	}

	
	public function scheduleDelayedRepeatingTask(Task $task, $delay, $period){
		return $this->addTask($task, (int) $delay, (int) $period);
	}

	
	public function cancelTask($taskId){
		if($taskId !== null and isset($this->tasks[$taskId])){
			$this->tasks[$taskId]->cancel();
			unset($this->tasks[$taskId]);
		}
	}

	
	public function cancelTasks(Plugin $plugin){
		foreach($this->tasks as $taskId => $task){
			$ptask = $task->getTask();
			if($ptask instanceof PluginTask and $ptask->getOwner() === $plugin){
				$task->cancel();
				unset($this->tasks[$taskId]);
			}
		}
	}

	public function cancelAllTasks(){
		foreach($this->tasks as $task){
			$task->cancel();
		}
		$this->tasks = [];
		$this->asyncPool->removeTasks();
		while(!$this->queue->isEmpty()){
			$this->queue->extract();
		}
		$this->ids = 1;
	}

	
	public function isQueued($taskId){
		return isset($this->tasks[$taskId]);
	}

	
	private function addTask(Task $task, $delay, $period){
		if($task instanceof PluginTask){
			if(!($task->getOwner() instanceof Plugin)){
				throw new PluginException("Invalid owner of PluginTask " . get_class($task));
			}elseif(!$task->getOwner()->isEnabled()){
				throw new PluginException("Plugin '" . $task->getOwner()->getName() . "' attempted to register a task while disabled");
			}
		}elseif($task instanceof CallbackTask and Server::getInstance()->getProperty("settings.deprecated-verbose", true)){
			$callable = $task->getCallable();
			if(is_array($callable)){
				if(is_object($callable[0])){
					$taskName = "Callback#" . get_class($callable[0]) . "::" . $callable[1];
				}else{
					$taskName = "Callback#" . $callable[0] . "::" . $callable[1];
				}
			}else{
				$taskName = "Callback#" . $callable;
			}
			
		}

		if($delay <= 0){
			$delay = -1;
		}

		if($period <= -1){
			$period = -1;
		}elseif($period < 1){
			$period = 1;
		}

		return $this->handle(new TaskHandler(get_class($task), $task, $this->nextId(), $delay, $period));
	}

	private function handle(TaskHandler $handler){
		if($handler->isDelayed()){
			$nextRun = $this->currentTick + $handler->getDelay();
		}else{
			$nextRun = $this->currentTick;
		}

		$handler->setNextRun($nextRun);
		$this->tasks[$handler->getTaskId()] = $handler;
		$this->queue->insert($handler, $nextRun);

		return $handler;
	}

	
	public function mainThreadHeartbeat($currentTick){
		$this->currentTick = $currentTick;
		while($this->isReady($this->currentTick)){
			
			$task = $this->queue->extract();
			if($task->isCancelled()){
				unset($this->tasks[$task->getTaskId()]);
				continue;
			}else{
				
				try{
					$task->run($this->currentTick);
				}catch(\Throwable $e){
					Server::getInstance()->getLogger()->critical("Could not execute task " . $task->getTaskName() . ": " . $e->getMessage());
					$logger = Server::getInstance()->getLogger();
					if($logger instanceof MainLogger){
						$logger->logException($e);
					}
				}
				
			}
			if($task->isRepeating()){
				$task->setNextRun($this->currentTick + $task->getPeriod());
				$this->queue->insert($task, $this->currentTick + $task->getPeriod());
			}else{
				$task->remove();
				unset($this->tasks[$task->getTaskId()]);
			}
		}

		$this->asyncPool->collectTasks();
	}

	private function isReady($currentTicks){
		return count($this->tasks) > 0 and $this->queue->current()->getNextRun() <= $currentTicks;
	}

	
	private function nextId(){
		return $this->ids++;
	}

}
