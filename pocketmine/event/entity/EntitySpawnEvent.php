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

use pocketmine\entity\Creature;
use pocketmine\entity\Entity;
use pocketmine\entity\Human;
use pocketmine\entity\Item;
use pocketmine\entity\Projectile;
use pocketmine\entity\Vehicle;

class EntitySpawnEvent extends EntityEvent{
	public static $handlerList = null;

	private $entityType;

	
	public function __construct(Entity $entity){
		$this->entity = $entity;
		$this->entityType = $entity::NETWORK_ID;
	}

	
	public function getPosition(){
		return $this->entity->getPosition();
	}

	
	public function getType(){
		return $this->entityType;
	}

	
	public function isCreature(){
		return $this->entity instanceof Creature;
	}

	
	public function isHuman(){
		return $this->entity instanceof Human;
	}

	
	public function isProjectile(){
		return $this->entity instanceof Projectile;
	}

	
	public function isVehicle(){
		return $this->entity instanceof Vehicle;
	}

	
	public function isItem(){
		return $this->entity instanceof Item;
	}

}