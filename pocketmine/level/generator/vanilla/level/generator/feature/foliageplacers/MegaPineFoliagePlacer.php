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
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\utils\Random;
use pocketmine\level\generator\vanilla\utils\valueproviders\IntProvider;
use function floor;

class MegaPineFoliagePlacer extends FoliagePlacer{
	public function __construct(
		IntProvider $radius,
		IntProvider $offset,
		private IntProvider $crownHeight
	){
		parent::__construct($radius, $offset);
	}

	public function type() : FoliagePlacerType {
		return FoliagePlacerType::MEGA_PINE_FOLIAGE_PLACER;
	}

	public function createFoliage(ChunkManager $level, CollectorSetter $foliageSetter, Random $random, TreeConfiguration $config, int $treeHeight, FoliageAttachment $foliageAttachment, int $foliageHeight, int $leafRadius, int $offset) : void{
		$foliagePos = $foliageAttachment->pos();
		$prevRadius = 0;

		for ($yy = $foliagePos->getY() - $foliageHeight + $offset; $yy <= $foliagePos->getY() + $offset; $yy++) {
			$yo = $foliagePos->getY() - $yy;
			$smoothRadius = (int) ($leafRadius + $foliageAttachment->radiusOffset() + floor((float) $yo / $foliageHeight * 3.5));
			if ($yo > 0 && $smoothRadius == $prevRadius && ($yy & 1) == 0) {
				$jaggedRadius = $smoothRadius + 1;
			} else {
				$jaggedRadius = $smoothRadius;
			}

			$this->placeLeavesRow($level, $foliageSetter, $random, $config, new Vector3($foliagePos->getX(), $yy, $foliagePos->getZ()), $jaggedRadius, 0, $foliageAttachment->doubleTrunk());
			$prevRadius = $smoothRadius;
		}
	}

	public function foliageHeight(Random $random, int $treeHeight, TreeConfiguration $config) : int{
		return $this->crownHeight->sample($random);
	}

	public function shouldSkipLocation(Random $random, int $dx, int $y, int $dz, int $currentRadius, bool $doubleTrunk) : bool{
		return $dx + $dz >= 7 || $dx * $dx + $dz * $dz > $currentRadius * $currentRadius;
	}
}
