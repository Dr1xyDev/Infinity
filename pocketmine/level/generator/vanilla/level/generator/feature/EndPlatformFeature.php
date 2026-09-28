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

namespace pocketmine\level\generator\vanilla\level\generator\feature;

use pocketmine\level\generator\vanilla\block\BlockFactory;
use pocketmine\level\generator\vanilla\block\BlockIds;

class EndPlatformFeature extends Feature {

	public function place(FeaturePlaceContext $context) : bool{
		$level = $context->level();
		$origin = $context->origin();

		$obsidian = BlockFactory::get(BlockIds::OBSIDIAN);
		$air = BlockFactory::get(BlockIds::AIR);
		for ($dz = -2; $dz <= 2; $dz++) {
			for ($dx = -2; $dx <= 2; $dx++) {
				for ($dy = -1; $dy < 3; $dy++) {
					$blockPos = $origin->add($dx, $dy, $dz);
					$block = $dy == -1 ? $obsidian : $air;
					if (!$level->getBlockAt($blockPos->getX(), $blockPos->getY(), $blockPos->getZ())->isSameType($block)) {
						//TODO: destroyBlock (dropResources)

						$level->setBlockAt($blockPos->getX(), $blockPos->getY(), $blockPos->getZ(), $block);
					}
				}
			}
		}

		return true;
	}
}
