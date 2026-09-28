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
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\utils\Random;

class GiantTrunkPlacer extends TrunkPlacer{

	public function type() : TrunkPlacerType {
		return TrunkPlacerType::GIANT_TRUNK_PLACER;
	}

	public function placeTrunk(ChunkManager $level, Setter $trunkSetter, Random $random, int $treeHeight, Vector3 $origin, TreeConfiguration $config) : array {
		$below = $origin->down();
		$this->setDirtAt($level, $trunkSetter, $random, $below, $config);
		$this->setDirtAt($level, $trunkSetter, $random, $below->east(), $config);
		$this->setDirtAt($level, $trunkSetter, $random, $below->south(), $config);
		$this->setDirtAt($level, $trunkSetter, $random, $below->south()->east(), $config);

		for ($hh = 0; $hh < $treeHeight; $hh++) {
			$this->placeLogIfFreeWithOffset($level, $trunkSetter, $random, $config, $origin, 0, $hh, 0);
			if ($hh < $treeHeight - 1) {
				$this->placeLogIfFreeWithOffset($level, $trunkSetter, $random, $config, $origin, 1, $hh, 0);
				$this->placeLogIfFreeWithOffset($level, $trunkSetter, $random, $config, $origin, 1, $hh, 1);
				$this->placeLogIfFreeWithOffset($level, $trunkSetter, $random, $config, $origin, 0, $hh, 1);
			}
		}

		return [new FoliageAttachment($origin->up($treeHeight), 0, true)];
	}

	public function placeLogIfFreeWithOffset(ChunkManager $level, Setter $trunkSetter, Random $random, TreeConfiguration $config, Vector3 $treePos, int $x, int $y, int $z,) : void {
		$this->placeLogIfFree($level, $trunkSetter, $random, $treePos->add($x, $y, $z), $config);
	}
}
