<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

namespace pocketmine\event\entity;

use pocketmine\entity\Living;
use pocketmine\item\Item;

class EntityDeathEvent extends EntityEvent{
	public static $handlerList = null;

	
	private $drops = [];

	
	public function __construct(Living $entity, array $drops = []){
		$this->entity = $entity;
		$this->drops = $drops;
	}

	
	public function getEntity(){
		return $this->entity;
	}

	
	public function getDrops(){
		return $this->drops;
	}

	
	public function setDrops(array $drops){
		$this->drops = $drops;
	}

}