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

use pocketmine\level\generator\vanilla\block\BlockIds;
use pocketmine\level\generator\vanilla\block\Snow;
use pocketmine\level\generator\vanilla\math\Facing;

class IcePathFeature extends AbstractSphereReplaceFeature {

	public function place(FeaturePlaceContext $context) : bool{
		$origin = $context->origin();

		$block = $context->level()->getBlockAt($origin->getFloorX(), $origin->getFloorY(), $origin->getFloorZ());
		while ($block->getId() === BlockIds::AIR && $block->getY() > 2) {
			$block = $block->getSide(Facing::DOWN);
		}

		return ($block instanceof Snow) && parent::place($context);
	}
}
