<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

namespace pocketmine\event\player;

use pocketmine\event\Event;
use pocketmine\network\SourceInterface;
use pocketmine\Player;

class PlayerCreationEvent extends Event{
	public static $handlerList = null;

	
	private $interface;
	
	private $clientId;
	
	private $address;
	
	private $port;

	
	private $baseClass;
	
	private $playerClass;

	
	public function __construct(SourceInterface $interface, $baseClass, $playerClass, $clientId, $address, $port){
		$this->interface = $interface;
		$this->clientId = $clientId;
		$this->address = $address;
		$this->port = $port;

		if(!is_a($baseClass, Player::class, true)){
			throw new \RuntimeException("Base class $baseClass must extend " . Player::class);
		}

		$this->baseClass = $baseClass;

		if(!is_a($playerClass, Player::class, true)){
			throw new \RuntimeException("Class $playerClass must extend " . Player::class);
		}

		$this->playerClass = $playerClass;
	}

	
	public function getInterface(){
		return $this->interface;
	}

	
	public function getAddress(){
		return $this->address;
	}

	
	public function getPort(){
		return $this->port;
	}

	
	public function getClientId(){
		return $this->clientId;
	}

	
	public function getBaseClass(){
		return $this->baseClass;
	}

	
	public function setBaseClass($class){
		if(!is_a($class, $this->baseClass, true)){
			throw new \RuntimeException("Base class $class must extend " . $this->baseClass);
		}

		$this->baseClass = $class;
	}

	
	public function getPlayerClass(){
		return $this->playerClass;
	}

	
	public function setPlayerClass($class){
		if(!is_a($class, $this->baseClass, true)){
			throw new \RuntimeException("Class $class must extend " . $this->baseClass);
		}

		$this->playerClass = $class;
	}

}