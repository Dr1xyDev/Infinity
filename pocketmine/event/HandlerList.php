<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

namespace pocketmine\event;

use pocketmine\plugin\Plugin;
use pocketmine\plugin\RegisteredListener;

class HandlerList{

	
	private $handlers = null;

	
	private $handlerSlots = [];

	
	private static $allLists = [];

	public static function bakeAll(){
		foreach(self::$allLists as $h){
			$h->bake();
		}
	}

	
	public static function unregisterAll($object = null){
		if($object instanceof Listener or $object instanceof Plugin){
			foreach(self::$allLists as $h){
				$h->unregister($object);
			}
		}else{
			foreach(self::$allLists as $h){
				foreach($h->handlerSlots as $key => $list){
					$h->handlerSlots[$key] = [];
				}
				$h->handlers = null;
			}
		}
	}

	public function __construct(){
		$this->handlerSlots = [
			EventPriority::LOWEST => [],
			EventPriority::LOW => [],
			EventPriority::NORMAL => [],
			EventPriority::HIGH => [],
			EventPriority::HIGHEST => [],
			EventPriority::MONITOR => []
		];
		self::$allLists[] = $this;
	}

	
	public function register(RegisteredListener $listener){
		if($listener->getPriority() < EventPriority::MONITOR or $listener->getPriority() > EventPriority::LOWEST){
			return;
		}
		if(isset($this->handlerSlots[$listener->getPriority()][spl_object_hash($listener)])){
			throw new \InvalidStateException("This listener is already registered to priority " . $listener->getPriority());
		}
		$this->handlers = null;
		$this->handlerSlots[$listener->getPriority()][spl_object_hash($listener)] = $listener;
	}

	
	public function registerAll(array $listeners){
		foreach($listeners as $listener){
			$this->register($listener);
		}
	}

	
	public function unregister($object){
		if($object instanceof Plugin or $object instanceof Listener){
			$changed = false;
			foreach($this->handlerSlots as $priority => $list){
				foreach($list as $hash => $listener){
					if(($object instanceof Plugin and $listener->getPlugin() === $object)
						or ($object instanceof Listener and $listener->getListener() === $object)
					){
						unset($this->handlerSlots[$priority][$hash]);
						$changed = true;
					}
				}
			}
			if($changed === true){
				$this->handlers = null;
			}
		}elseif($object instanceof RegisteredListener){
			if(isset($this->handlerSlots[$object->getPriority()][spl_object_hash($object)])){
				unset($this->handlerSlots[$object->getPriority()][spl_object_hash($object)]);
				$this->handlers = null;
			}
		}
	}

	public function bake(){
		if($this->handlers !== null){
			return;
		}
		$entries = [];
		foreach($this->handlerSlots as $list){
			foreach($list as $hash => $listener){
				$entries[$hash] = $listener;
			}
		}
		$this->handlers = $entries;
	}

	
	public function getRegisteredListeners($plugin = null){
		if($plugin !== null){
			$listeners = [];
			foreach($this->getRegisteredListeners(null) as $hash => $listener){
				if($listener->getPlugin() === $plugin){
					$listeners[$hash] = $plugin;
				}
			}

			return $listeners;
		}else{
			while(($handlers = $this->handlers) === null){
				$this->bake();
			}

			return $handlers;
		}
	}

	
	public static function getHandlerLists(){
		return self::$allLists;
	}

}
