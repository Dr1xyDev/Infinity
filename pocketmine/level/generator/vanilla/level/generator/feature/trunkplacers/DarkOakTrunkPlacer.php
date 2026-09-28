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
use function count;

class DarkOakTrunkPlacer extends TrunkPlacer{

	public function type() : TrunkPlacerType {
		return TrunkPlacerType::DARK_OAK_TRUNK_PLACER;
	}

	public function placeTrunk(ChunkManager $level, Setter $trunkSetter, Random $random, int $treeHeight, Vector3 $origin, TreeConfiguration $config) : array {
		$attachments = [];

		$below = $origin->down();
		$this->setDirtAt($level, $trunkSetter, $random, $below, $config);
		$this->setDirtAt($level, $trunkSetter, $random, $below->east(), $config);
		$this->setDirtAt($level, $trunkSetter, $random, $below->south(), $config);
		$this->setDirtAt($level, $trunkSetter, $random, $below->south()->east(), $config);

		$leanDirection = Facing::HORIZONTAL[$random->nextRange(0, count(Facing::HORIZONTAL) - 1)];
		$leanHeight = $treeHeight - $random->nextBoundedInt(4);
		$leanSteps = 2 - $random->nextBoundedInt(3);
		$x = $origin->getX();
		$y = $origin->getY();
		$z = $origin->getZ();
		$tx = $x;
		$tz = $z;
		$ey = $y + $treeHeight - 1;

		for ($dy = 0; $dy < $treeHeight; $dy++) {
			if ($dy >= $leanHeight && $leanSteps > 0) {
				$tx += Facing::OFFSET[$leanDirection][0];
				$tz += Facing::OFFSET[$leanDirection][2];
				$leanSteps--;
			}

			$yy = $y + $dy;
			$blockPos = new Vector3($tx, $yy, $tz);
			if (TreeFeature::isAirOrLeaves($level, $blockPos)) {
				$this->placeLog($level, $trunkSetter, $random, $blockPos, $config);
				$this->placeLog($level, $trunkSetter, $random, $blockPos->east(), $config);
				$this->placeLog($level, $trunkSetter, $random, $blockPos->south(), $config);
				$this->placeLog($level, $trunkSetter, $random, $blockPos->east()->south(), $config);
			}
		}

		$attachments[] = new FoliageAttachment(new Vector3($tx, $ey, $tz), 0, true);

		for ($ox = -1; $ox <= 2; $ox++) {
			for ($oz = -1; $oz <= 2; $oz++) {
				if (($ox < 0 || $ox > 1 || $oz < 0 || $oz > 1) && $random->nextBoundedInt(3) <= 0) {
					$length = $random->nextBoundedInt(3) + 2;

					for ($branchY = 0; $branchY < $length; $branchY++) {
						$this->placeLog($level, $trunkSetter, $random, new Vector3($x + $ox, $ey - $branchY - 1, $z + $oz), $config);
					}

					$attachments[] = new FoliageAttachment(new Vector3($x + $ox, $ey, $z + $oz), 0, false);
				}
			}
		}

		return $attachments;
	}
}
