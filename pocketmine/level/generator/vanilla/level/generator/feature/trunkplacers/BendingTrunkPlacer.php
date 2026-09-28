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

namespace pocketmine\level\generator\vanilla\level\generator\feature\trunkplacers;

use pocketmine\level\generator\vanilla\level\ChunkManager;
use pocketmine\level\generator\vanilla\level\generator\feature\configurations\TreeConfiguration;
use pocketmine\level\generator\vanilla\level\generator\feature\setter\Setter;
use pocketmine\level\generator\vanilla\level\generator\feature\TreeFeature;
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\utils\Random;
use pocketmine\level\generator\vanilla\utils\valueproviders\IntProvider;
use function count;

class BendingTrunkPlacer extends TrunkPlacer{
	public function __construct(
		int $baseHeight,
		int $heightRandA,
		int $heightRandB,
		private int $minHeightForLeaves,
		private IntProvider $bendLength
	){
		parent::__construct($baseHeight, $heightRandA, $heightRandB);
	}

	public function type() : TrunkPlacerType {
		return TrunkPlacerType::BENDING_TRUNK_PLACER;
	}

	public function placeTrunk(ChunkManager $level, Setter $trunkSetter, Random $random, int $treeHeight, Vector3 $origin, TreeConfiguration $config) : array {
		$direction = Facing::HORIZONTAL[$random->nextRange(0, count(Facing::HORIZONTAL) - 1)];
		$logHeight = $treeHeight - 1;
		$pos = clone $origin;
		$belowPos = $pos->down();
		$this->setDirtAt($level, $trunkSetter, $random, $belowPos, $config);
		$foliagePoints = [];

		for ($i = 0; $i <= $logHeight; $i++) {
			if ($i + 1 >= $logHeight + $random->nextBoundedInt(2)) {
				$pos = $pos->getSide($direction);
			}

			if (TreeFeature::validTreePos($level, $pos)) {
				$this->placeLog($level, $trunkSetter, $random, $pos, $config);
			}

			if ($i >= $this->minHeightForLeaves) {
				$foliagePoints[] = new FoliageAttachment(clone $pos, 0, false);
			}

			$pos = $pos->up();
		}

		$dirLength = $this->bendLength->sample($random);

		for ($i = 0; $i <= $dirLength; $i++) {
			if (TreeFeature::validTreePos($level, $pos)) {
				$this->placeLog($level, $trunkSetter, $random, $pos, $config);
			}

			$foliagePoints[] = new FoliageAttachment(clone $pos, 0, false);
			$pos = $pos->getSide($direction);
		}

		return $foliagePoints;
	}
}
