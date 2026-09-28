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
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\utils\Random;
use function count;

class ForkingTrunkPlacer extends TrunkPlacer{

	public function type() : TrunkPlacerType {
		return TrunkPlacerType::FORKING_TRUNK_PLACER;
	}

	public function placeTrunk(ChunkManager $level, Setter $trunkSetter, Random $random, int $treeHeight, Vector3 $origin, TreeConfiguration $config) : array {
		$this->setDirtAt($level, $trunkSetter, $random, $origin->down(), $config);
		$attachments = [];
		$leanDirection = Facing::HORIZONTAL[$random->nextRange(0, count(Facing::HORIZONTAL) - 1)];
		$leanHeight = $treeHeight - $random->nextBoundedInt(4) - 1;
		$leanSteps = 3 - $random->nextBoundedInt(3);
		$tx = $origin->getX();
		$tz = $origin->getZ();
		$ey = null;

		for ($yo = 0; $yo < $treeHeight; $yo++) {
			$yy = $origin->getY() + $yo;
			if ($yo >= $leanHeight && $leanSteps > 0) {
				$tx += Facing::OFFSET[$leanDirection][0];
				$tz += Facing::OFFSET[$leanDirection][2];
				$leanSteps--;
			}

			if ($this->placeLog($level, $trunkSetter, $random, new Vector3($tx, $yy, $tz), $config)) {
				$ey = $yy + 1;
			}
		}

		if ($ey !== null) {
			$attachments[] = new FoliageAttachment(new Vector3($tx, $ey, $tz), 1, false);
		}

		$tx = $origin->getX();
		$tz = $origin->getZ();
		$branchDirection = Facing::HORIZONTAL[$random->nextRange(0, count(Facing::HORIZONTAL) - 1)];
		if ($branchDirection != $leanDirection) {
			$branchPos = $leanHeight - $random->nextBoundedInt(2) - 1;
			$branchSteps = 1 + $random->nextBoundedInt(3);
			$ey = null;

			for ($yo = $branchPos; $yo < $treeHeight && $branchSteps > 0; $branchSteps--) {
				if ($yo >= 1) {
					$yyx = $origin->getY() + $yo;
					$tx += Facing::OFFSET[$branchDirection][0];
					$tz += Facing::OFFSET[$branchDirection][2];
					if ($this->placeLog($level, $trunkSetter, $random, new Vector3($tx, $yyx, $tz), $config)) {
						$ey = $yyx + 1;
					}
				}

				$yo++;
			}

			if ($ey !== null) {
				$attachments[] = new FoliageAttachment(new Vector3($tx, $ey, $tz), 0, false);
			}
		}

		return $attachments;
	}
}
