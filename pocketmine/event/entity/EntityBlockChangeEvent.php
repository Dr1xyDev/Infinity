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

use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\event\Cancellable;

class EntityBlockChangeEvent extends EntityEvent implements Cancellable{
	public static $handlerList = null;

	private $from;
	private $to;

	public function __construct(Entity $entity, Block $from, Block $to){
		$this->entity = $entity;
		$this->from = $from;
		$this->to = $to;
	}

	
	public function getBlock(){
		return $this->from;
	}

	
	public function getTo(){
		return $this->to;
	}

}