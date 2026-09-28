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
use pocketmine\level\generator\vanilla\utils\valueproviders\IntProvider;

class CherryFoliagePlacer extends FoliagePlacer{

	public function __construct(
		IntProvider $radius,
		IntProvider $offset,
		protected IntProvider $height,
		protected float $wideBottomLayerHoleChance,
		protected float $cornerHoleChance,
		protected float $hangingLeavesChance,
		protected float $hangingLeavesExtensionChance
	){
		parent::__construct($radius, $offset);
	}

	public function type() : FoliagePlacerType {
		return FoliagePlacerType::CHERRY_FOLIAGE_PLACER;
	}

	public function createFoliage(ChunkManager $level, CollectorSetter $foliageSetter, Random $random, TreeConfiguration $config, int $treeHeight, FoliageAttachment $foliageAttachment, int $foliageHeight, int $leafRadius, int $offset) : void{
		$doubleTrunk = $foliageAttachment->doubleTrunk();
		$foliagePos = $foliageAttachment->pos()->up($offset);
		$currentRadius = $leafRadius + $foliageAttachment->radiusOffset() - 1;
		$this->placeLeavesRow($level, $foliageSetter, $random, $config, $foliagePos, $currentRadius - 2, $foliageHeight - 3, $doubleTrunk);
		$this->placeLeavesRow($level, $foliageSetter, $random, $config, $foliagePos, $currentRadius - 1, $foliageHeight - 4, $doubleTrunk);

		for ($y = $foliageHeight - 5; $y >= 0; $y--) {
			$this->placeLeavesRow($level, $foliageSetter, $random, $config, $foliagePos, $currentRadius, $y, $doubleTrunk);
		}

		$this->placeLeavesRowWithHangingLeavesBelow(
			$level, $foliageSetter, $random, $config, $foliagePos, $currentRadius, -1, $doubleTrunk, $this->hangingLeavesChance, $this->hangingLeavesExtensionChance
		);
		$this->placeLeavesRowWithHangingLeavesBelow(
			$level, $foliageSetter, $random, $config, $foliagePos, $currentRadius - 1, -2, $doubleTrunk, $this->hangingLeavesChance, $this->hangingLeavesExtensionChance
		);
	}

	public function foliageHeight(Random $random, int $treeHeight, TreeConfiguration $config) : int{
		return $this->height->sample($random);
	}

	public function shouldSkipLocation(Random $random, int $dx, int $y, int $dz, int $currentRadius, bool $doubleTrunk) : bool{
		if ($y == -1 && ($dx == $currentRadius || $dz == $currentRadius) && $random->nextFloat() < $this->wideBottomLayerHoleChance) {
			return true;
		} else {
			$corner = $dx == $currentRadius && $dz == $currentRadius;
			$wideLayer = $currentRadius > 2;
			return $wideLayer
				? $corner || $dx + $dz > $currentRadius * 2 - 2 && $random->nextFloat() < $this->cornerHoleChance
				: $corner && $random->nextFloat() < $this->cornerHoleChance;
		}
	}
}
