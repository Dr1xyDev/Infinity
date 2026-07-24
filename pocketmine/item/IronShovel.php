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


class IronShovel extends Tool{
	public function __construct($meta = 0, $count = 1){
		parent::__construct(self::IRON_SHOVEL, $meta, $count, "Iron Shovel");
	}

	public function isShovel(){
		return Tool::TIER_IRON;
	}

	public function getAttackDamage(){
		return 4;
	}
}