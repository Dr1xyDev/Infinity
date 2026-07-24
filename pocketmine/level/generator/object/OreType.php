<?php

/*
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.0
 *               InfinityProject By @Dr1xyDev
 *   YT:         @Dr1xyDev
 *   GitHub:     github.com/Dr1xyDev/Infinity
*/

namespace pocketmine\level\generator\object;

use pocketmine\block\Block;

class OreType{
	public $material, $clusterCount, $clusterSize, $maxHeight, $minHeight;

	public function __construct(Block $material, $clusterCount, $clusterSize, $minHeight, $maxHeight){
		$this->material = $material;
		$this->clusterCount = (int) $clusterCount;
		$this->clusterSize = (int) $clusterSize;
		$this->maxHeight = (int) $maxHeight;
		$this->minHeight = (int) $minHeight;
	}
}