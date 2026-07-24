<?php

/*
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.0
 *               InfinityProject By @Dr1xyDev
 *   YT:         @Dr1xyDev
 *   GitHub:     github.com/Dr1xyDev/Infinity
*/

namespace pocketmine\inventory;

use pocketmine\tile\Dropper;

class DropperInventory extends ContainerInventory{
	public function __construct(Dropper $tile){
		parent::__construct($tile, InventoryType::get(InventoryType::DROPPER));
	}

	/**
	 * @return Dropper
	 */
	public function getHolder(){
		return $this->holder;
	}
}