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

use pocketmine\entity\Item;
use pocketmine\event\Cancellable;

class ItemDespawnEvent extends EntityEvent implements Cancellable{
	public static $handlerList = null;

	/**
	 * @param Item $item
	 */
	public function __construct(Item $item){
		$this->entity = $item;

	}

	/**
	 * @return Item
	 */
	public function getEntity(){
		return $this->entity;
	}

}