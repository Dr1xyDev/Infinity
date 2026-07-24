<?php

/*
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.0
 *               InfinityProject By @Dr1xyDev
 *   YT:         @Dr1xyDev
 *   GitHub:     github.com/Dr1xyDev/Infinity
*/

namespace pocketmine\event\entity;

use pocketmine\entity\Effect;
use pocketmine\entity\Entity;
use pocketmine\event\Cancellable;
use pocketmine\item\Potion;

class EntityDrinkPotionEvent extends EntityEvent implements Cancellable{
	
	public static $handlerList = null;
	
	/* @var Potion */
	private $potion;
	
	/* @var Effect[] */
	private $effects;
	
	public function __construct(Entity $entity, Potion $potion){
		$this->entity = $entity;
		$this->potion = $potion;
		$this->effects = $potion->getEffects();
	}
	
	public function getEffects(){
		return $this->effects;
	}
	
	public function getPotion(){
		return $this->potion;
	}
}
