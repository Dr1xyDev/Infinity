<?php

/*
 *
 *   _____       _                          _
 *  / ____|     | |                        (_)
 * | (___  _   _| |__  _ __ ___   __ _ _ __ _ _ __   ___
 *  \___ \| | | | '_ \| '_ ` _ \ / _` | '__| | '_ \ / _ \
 *  ____) | |_| | |_) | | | | | | (_| | |  | | | | |  __/
 * |_____/ \__,_|_.__/|_| |_| |_|\__,_|_|  |_|_| |_|\___|
 *
 * This program is private software. No license required.
 * Publication of this program is forbidden and will be punished.
 *
 * @author SEMENNEJO
 * @link vk.com/vk.snikers && t.me/semennejo
 *
 *
 */

declare(strict_types=1);

namespace pocketmine\level\generator\vanilla\level\generator\feature\foliageplacers;

use pocketmine\level\generator\vanilla\level\ChunkManager;
use pocketmine\level\generator\vanilla\level\generator\feature\configurations\TreeConfiguration;
use pocketmine\level\generator\vanilla\level\generator\feature\setter\CollectorSetter;
use pocketmine\level\generator\vanilla\level\generator\feature\trunkplacers\FoliageAttachment;
use pocketmine\level\generator\vanilla\utils\Random;

class AcaciaFoliagePlacer extends FoliagePlacer{

	public function type() : FoliagePlacerType {
		return FoliagePlacerType::ACACIA_FOLIAGE_PLACER;
	}

	public function createFoliage(ChunkManager $level, CollectorSetter $foliageSetter, Random $random, TreeConfiguration $config, int $treeHeight, FoliageAttachment $foliageAttachment, int $foliageHeight, int $leafRadius, int $offset) : void{
		$doubleTrunk = $foliageAttachment->doubleTrunk();
		$foliagePos = $foliageAttachment->pos()->up($offset);
		$this->placeLeavesRow($level, $foliageSetter, $random, $config, $foliagePos, $leafRadius + $foliageAttachment->radiusOffset(), -1 - $foliageHeight, $doubleTrunk);
		$this->placeLeavesRow($level, $foliageSetter, $random, $config, $foliagePos, $leafRadius - 1, -$foliageHeight, $doubleTrunk);
		$this->placeLeavesRow($level, $foliageSetter, $random, $config, $foliagePos, $leafRadius + $foliageAttachment->radiusOffset() - 1, 0, $doubleTrunk);
	}

	public function foliageHeight(Random $random, int $treeHeight, TreeConfiguration $config) : int{
		return 0;
	}

	public function shouldSkipLocation(Random $random, int $dx, int $y, int $dz, int $currentRadius, bool $doubleTrunk) : bool{
		return $y == 0 ? ($dx > 1 || $dz > 1) && $dx != 0 && $dz != 0 : $dx == $currentRadius && $dz == $currentRadius && $currentRadius > 0;
	}
}
