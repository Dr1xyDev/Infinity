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
use pocketmine\level\Position;

class EntityExplodeEvent extends EntityEvent implements Cancellable{
	public static $handlerList = null;

	
	protected $position;

	
	protected $blocks;

	
	protected $yield;

	
	public function __construct(Entity $entity, Position $position, array $blocks, $yield){
		$this->entity = $entity;
		$this->position = $position;
		$this->blocks = $blocks;
		$this->yield = $yield;
	}

	
	public function getPosition(){
		return $this->position;
	}

	
	public function getBlockList(){
		return $this->blocks;
	}

	
	public function setBlockList(array $blocks){
		$this->blocks = $blocks;
	}

	
	public function getYield(){
		return $this->yield;
	}

	
	public function setYield($yield){
		$this->yield = $yield;
	}

}