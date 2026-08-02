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

use pocketmine\entity\Entity;
use pocketmine\event\Cancellable;

class ExplosionPrimeEvent extends EntityEvent implements Cancellable{
	public static $handlerList = null;

	protected $force;
	private $blockBreaking;
	private $dropItem;

	
	public function __construct(Entity $entity, $force, bool $dropItem){
		$this->entity = $entity;
		$this->force = $force;
		$this->blockBreaking = true;
		$this->dropItem = $dropItem;
	}

	public function setDropItem(bool $dropItem){
		$this->dropItem = $dropItem;
	}

	public function dropItem() : bool{
		return $this->dropItem;
	}

	
	public function getForce(){
		return $this->force;
	}

	public function setForce($force){
		$this->force = (float) $force;
	}

	
	public function isBlockBreaking(){
		return $this->blockBreaking;
	}

	
	public function setBlockBreaking($affectsBlocks){
		$this->blockBreaking = (bool) $affectsBlocks;
	}

}