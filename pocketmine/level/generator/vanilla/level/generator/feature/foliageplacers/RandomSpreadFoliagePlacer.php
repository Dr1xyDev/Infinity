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

class RandomSpreadFoliagePlacer extends FoliagePlacer{

	public function __construct(
		IntProvider $radius,
		IntProvider $offset,
		private IntProvider $foliageHeight,
		private int $leafPlacementAttempts
	){
		parent::__construct($radius, $offset);
	}

	public function type() : FoliagePlacerType {
		return FoliagePlacerType::RANDOM_SPREAD_FOLIAGE_PLACER;
	}

	public function createFoliage(ChunkManager $level, CollectorSetter $foliageSetter, Random $random, TreeConfiguration $config, int $treeHeight, FoliageAttachment $foliageAttachment, int $foliageHeight, int $leafRadius, int $offset) : void{
		$origin = $foliageAttachment->pos();
		$pos = clone $origin;

		for ($i = 0; $i < $this->leafPlacementAttempts; $i++) {
			$pos = $origin->add(
				$random->nextBoundedInt($leafRadius) - $random->nextBoundedInt($leafRadius),
				$random->nextBoundedInt($foliageHeight) - $random->nextBoundedInt($foliageHeight),
				$random->nextBoundedInt($leafRadius) - $random->nextBoundedInt($leafRadius)
			);
			$this->tryPlaceLeaf($level, $foliageSetter, $random, $config, $pos);
		}
	}

	public function foliageHeight(Random $random, int $treeHeight, TreeConfiguration $config) : int{
		return $this->foliageHeight->sample($random);
	}

	public function shouldSkipLocation(Random $random, int $dx, int $y, int $dz, int $currentRadius, bool $doubleTrunk) : bool{
		return false;
	}
}
