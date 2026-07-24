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

use pocketmine\entity\Projectile;
use pocketmine\event\Cancellable;

class ProjectileLaunchEvent extends EntityEvent implements Cancellable{
	public static $handlerList = null;

	/**
	 * @param Projectile $entity
	 */
	public function __construct(Projectile $entity){
		$this->entity = $entity;

	}

	/**
	 * @return Projectile
	 */
	public function getEntity(){
		return $this->entity;
	}

}