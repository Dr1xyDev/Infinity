<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

namespace pocketmine\plugin;

use pocketmine\event\Cancellable;
use pocketmine\event\Event;
use pocketmine\event\Listener;
use pocketmine\event\TimingsHandler;

class RegisteredListener{

	
	private $listener;

	
	private $priority;

	
	private $plugin;

	
	private $executor;

	
	private $ignoreCancelled;

	
	private $timings;

	
	public function __construct(Listener $listener, EventExecutor $executor, $priority, Plugin $plugin, $ignoreCancelled, TimingsHandler $timings){
		$this->listener = $listener;
		$this->priority = $priority;
		$this->plugin = $plugin;
		$this->executor = $executor;
		$this->ignoreCancelled = $ignoreCancelled;
		$this->timings = $timings;
	}

	
	public function getListener(){
		return $this->listener;
	}

	
	public function getPlugin(){
		return $this->plugin;
	}

	
	public function getPriority(){
		return $this->priority;
	}

	
	public function callEvent(Event $event){
		if($event instanceof Cancellable and $event->isCancelled() and $this->isIgnoringCancelled()){
			return;
		}
		
		$this->executor->execute($this->listener, $event);
		
	}

	public function __destruct(){
		$this->timings->remove();
	}

	
	public function isIgnoringCancelled(){
		return $this->ignoreCancelled === true;
	}
}