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

class EntityDamageByChildEntityEvent extends EntityDamageByEntityEvent{

	
	private $childEntity;

	
	public function __construct(Entity $damager, Entity $childEntity, Entity $entity, $cause, $damage){
		$this->childEntity = $childEntity;
		parent::__construct($damager, $entity, $cause, $damage);
	}

	
	public function getChild(){
		return $this->childEntity;
	}

}