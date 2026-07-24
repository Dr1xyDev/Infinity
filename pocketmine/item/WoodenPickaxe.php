<?php

/*
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.0
 *               InfinityProject By @Dr1xyDev
 *   YT:         @Dr1xyDev
 *   GitHub:     github.com/Dr1xyDev/Infinity
*/

namespace pocketmine\item;


class WoodenPickaxe extends Tool{
	public function __construct($meta = 0, $count = 1){
		parent::__construct(self::WOODEN_PICKAXE, $meta, $count, "Wooden Pickaxe");
	}

	public function isPickaxe(){
		return Tool::TIER_WOODEN;
	}

	public function getAttackDamage(){
		return 3;
	}
}
