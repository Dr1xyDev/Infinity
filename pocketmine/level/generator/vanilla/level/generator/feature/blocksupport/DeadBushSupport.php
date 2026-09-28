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

namespace pocketmine\level\generator\vanilla\level\generator\feature\blocksupport;

use pocketmine\level\generator\vanilla\block\BlockIds;
use pocketmine\level\generator\vanilla\block\Mud;
use pocketmine\level\generator\vanilla\block\Sand;
use pocketmine\level\generator\vanilla\level\ChunkManager;
use pocketmine\level\generator\vanilla\math\Vector3;

class DeadBushSupport implements BlockSupport {

	public function isValidPosition(ChunkManager $level, Vector3 $origin) : bool {
		$down = $level->getBlockAt($origin->x, $origin->y - 1, $origin->z);
		return
			$down instanceof Sand ||
			$down instanceof Mud ||
			match ($down->getId()) {
				//can't use DIRT tag here because it includes farmland
				BlockIds::PODZOL,
				BlockIds::MYCELIUM,
				BlockIds::DIRT,
				BlockIds::GRASS,
				BlockIds::HARDENED_CLAY,
				BlockIds::MOSS_BLOCK,
				BlockIds::STAINED_CLAY => true,
				default => false,
			};
	}
}
