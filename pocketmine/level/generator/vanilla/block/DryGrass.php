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

namespace pocketmine\level\generator\vanilla\block;

use pocketmine\level\generator\vanilla\block\utils\BushTrait;
use pocketmine\level\generator\vanilla\block\utils\StaticSupportTrait;
use pocketmine\level\generator\vanilla\math\Facing;

class DryGrass extends Flowable
{
	use BushTrait;
	use StaticSupportTrait;

	protected function canBeSupportedAt(Block $block) : bool{
		$supportBlock = $block->getSide(Facing::DOWN);
		return
			$supportBlock instanceof Grass ||
			$supportBlock instanceof Mycelium ||
			$supportBlock instanceof Podzol ||
			$supportBlock instanceof Dirt ||
			$supportBlock instanceof DirtWithRoots ||
			$supportBlock instanceof Farmland ||
			$supportBlock instanceof HardenedClay ||
			$supportBlock instanceof Sand ||
			$supportBlock instanceof SuspiciousSand ||
			$supportBlock instanceof Mud ||
			$supportBlock instanceof Moss ||
			$supportBlock instanceof PaleMoss; //TODO: MUDDY_MANGROVE_ROOTS
	}
}
