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


class ChainBoots extends Armor{
	public function __construct($meta = 0, $count = 1){
		parent::__construct(self::CHAIN_BOOTS, $meta, $count, "Chainmail Boots");
	}

	public function getArmorTier(){
		return Armor::TIER_CHAIN;
	}

	public function getArmorType(){
		return Armor::TYPE_BOOTS;
	}

	public function getMaxDurability(){
		return 196;
	}

	public function getArmorValue(){
		return 1;
	}

	public function isBoots(){
		return true;
	}
}