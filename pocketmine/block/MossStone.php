<?php

/*
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.0
 *               InfinityProject By @Dr1xyDev
 *   YT:         @Dr1xyDev
 *   GitHub:     github.com/Dr1xyDev/Infinity
*/

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\item\Tool;

class MossStone extends Solid{

	protected $id = self::MOSS_STONE;

	public function __construct($meta = 0){
		$this->meta = (int) $meta;
	}

	public function getName() : string{
		return "Moss Stone";
	}

	public function getHardness() {
		return 2;
	}

	public function getToolType(){
		return Tool::TYPE_PICKAXE;
	}

	public function getDrops(Item $item) : array {
		if($item->isPickaxe() >= 1){
			return [
				[Item::MOSS_STONE, $this->meta, 1],
			];
		}else{
			return [];
		}
	}
}