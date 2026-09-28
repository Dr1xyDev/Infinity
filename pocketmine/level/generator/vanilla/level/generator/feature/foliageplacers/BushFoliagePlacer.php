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

class BushFoliagePlacer extends BlobFoliagePlacer{

	public function type() : FoliagePlacerType {
		return FoliagePlacerType::BUSH_FOLIAGE_PLACER;
	}

	public function createFoliage(ChunkManager $level, CollectorSetter $foliageSetter, Random $random, TreeConfiguration $config, int $treeHeight, FoliageAttachment $foliageAttachment, int $foliageHeight, int $leafRadius, int $offset) : void{
		for ($yo = $offset; $yo >= $offset - $foliageHeight; $yo--) {
			$currentRadius = $leafRadius + $foliageAttachment->radiusOffset() - 1 - $yo;
			$this->placeLeavesRow($level, $foliageSetter, $random, $config, $foliageAttachment->pos(), $currentRadius, $yo, $foliageAttachment->doubleTrunk());
		}
	}

	public function shouldSkipLocation(Random $random, int $dx, int $y, int $dz, int $currentRadius, bool $doubleTrunk) : bool{
		return $dx == $currentRadius && $dz == $currentRadius && $random->nextBoundedInt(2) == 0;
	}
}
