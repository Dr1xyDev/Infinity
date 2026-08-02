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

class EntityCombustByEntityEvent extends EntityCombustEvent{

	protected $combuster;

	
	public function __construct(Entity $combuster, Entity $combustee, $duration, $ProtectLevel = 0){
		parent::__construct($combustee, $duration, $ProtectLevel);
		$this->combuster = $combuster;
	}

	
	public function getCombuster(){
		return $this->combuster;
	}

}