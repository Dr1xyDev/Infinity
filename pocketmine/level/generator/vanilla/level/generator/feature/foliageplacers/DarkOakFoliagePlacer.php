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

class DarkOakFoliagePlacer extends FoliagePlacer{
	public function type() : FoliagePlacerType {
		return FoliagePlacerType::DARK_OAK_FOLIAGE_PLACER;
	}

	public function createFoliage(ChunkManager $level, CollectorSetter $foliageSetter, Random $random, TreeConfiguration $config, int $treeHeight, FoliageAttachment $foliageAttachment, int $foliageHeight, int $leafRadius, int $offset) : void{
		$pos = $foliageAttachment->pos()->up($offset);
		$doubleTrunk = $foliageAttachment->doubleTrunk();
		if ($doubleTrunk) {
			$this->placeLeavesRow($level, $foliageSetter, $random, $config, $pos, $leafRadius + 2, -1, $doubleTrunk);
			$this->placeLeavesRow($level, $foliageSetter, $random, $config, $pos, $leafRadius + 3, 0, $doubleTrunk);
			$this->placeLeavesRow($level, $foliageSetter, $random, $config, $pos, $leafRadius + 2, 1, $doubleTrunk);
			if ($random->nextBoolean()) {
				$this->placeLeavesRow($level, $foliageSetter, $random, $config, $pos, $leafRadius, 2, $doubleTrunk);
			}
		} else {
			$this->placeLeavesRow($level, $foliageSetter, $random, $config, $pos, $leafRadius + 2, -1, $doubleTrunk);
			$this->placeLeavesRow($level, $foliageSetter, $random, $config, $pos, $leafRadius + 1, 0, $doubleTrunk);
		}
	}

	public function foliageHeight(Random $random, int $treeHeight, TreeConfiguration $config) : int{
		return 4;
	}

	public function shouldSkipLocation(Random $random, int $dx, int $y, int $dz, int $currentRadius, bool $doubleTrunk) : bool{
		return !($y != 0 || !$doubleTrunk || $dx != -$currentRadius && $dx < $currentRadius || $dz != -$currentRadius && $dz < $currentRadius) || parent::shouldSkipLocationSigned($random, $dx, $y, $dz, $currentRadius, $doubleTrunk);
	}

	public function shouldSkipLocationSigned(Random $random, int $dx, int $y, int $dz, int $currentRadius, bool $doubleTrunk) : bool{
		if ($y == -1 && !$doubleTrunk) {
			return $dx == $currentRadius && $dz == $currentRadius;
		} else {
			return $y == 1 && $dx + $dz > $currentRadius * 2 - 2;
		}
	}
}
