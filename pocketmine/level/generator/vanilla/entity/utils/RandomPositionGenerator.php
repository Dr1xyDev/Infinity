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

namespace pocketmine\level\generator\vanilla\entity\utils;

use pocketmine\level\generator\vanilla\entity\Mob;
use pocketmine\level\generator\vanilla\math\Vector3;

use const PHP_INT_MIN;

class RandomPositionGenerator
{
	public static function findRandomTargetBlockAwayFrom(Mob $entity, int $xz, int $y, Vector3 $targetPos) : ?Vector3
	{
		return self::findRandomTargetBlock($entity, $xz, $y, $entity->subtractVector($targetPos));
	}

	public static function findRandomTargetBlock(Mob $entity, int $dxz, int $dy, ?Vector3 $targetPos = null) : ?Vector3
	{
		$currentWeight = PHP_INT_MIN;
		$currentPos = null;
		for ($i = 0; $i < 10; $i++) {
			$x = $entity->random->nextBoundedInt(2 * $dxz + 1) - $dxz;
			$y = $entity->random->nextBoundedInt(2 * $dy + 1) - $dy;
			$z = $entity->random->nextBoundedInt(2 * $dxz + 1) - $dxz;

			if ($targetPos === null || ($x * $targetPos->x + $z * $targetPos->z) > 0) {
				$targetVector = $entity->asVector3()->add($x, $y, $z);

				// TODO: remove this temp fix
				if (($maxY = $entity->level->getHeightMap($targetVector->getFloorX(), $targetVector->getFloorZ()) + 1) < $targetVector->y) {
					$targetVector->y = $maxY;
				}

				$weight = $entity->getBlockPathWeight($targetVector);
				if ($weight > $currentWeight) {
					$currentWeight = $weight;
					$currentPos = $targetVector;
				}
			}
		}

		return $currentPos;
	}
}
