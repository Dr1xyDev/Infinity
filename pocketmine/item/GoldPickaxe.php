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


class GoldPickaxe extends Tool{
	public function __construct($meta = 0, $count = 1){
		parent::__construct(self::GOLD_PICKAXE, $meta, $count, "Gold Pickaxe");
	}

	public function isPickaxe(){
		return Tool::TIER_GOLD;
	}

	public function getAttackDamage(){
		return 3;
	}
}
