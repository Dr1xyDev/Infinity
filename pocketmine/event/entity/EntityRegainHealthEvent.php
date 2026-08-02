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

class EntityRegainHealthEvent extends EntityEvent implements Cancellable{
	public static $handlerList = null;

	const CAUSE_REGEN = 0;
	const CAUSE_EATING = 1;
	const CAUSE_MAGIC = 2;
	const CAUSE_CUSTOM = 3;
	const CAUSE_SATURATION = 4;

	private $amount;
	private $reason;

	
	public function __construct(Entity $entity, $amount, $regainReason){
		$this->entity = $entity;
		$this->amount = $amount;
		$this->reason = (int) $regainReason;
	}

	
	public function getAmount(){
		return $this->amount;
	}

	
	public function setAmount($amount){
		$this->amount = $amount;
	}

	public function getRegainReason(){
		return $this->reason;
	}

}