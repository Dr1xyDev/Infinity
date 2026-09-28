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

namespace pocketmine\level\generator\vanilla\level\generator\feature\blockplacer;

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\level\ChunkManager;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\utils\Random;

class ColumnBlockPlacer implements BlockPlacer{

	public function __construct(
		private int $minSize,
		private int $extraSize
	){}

	public function place(ChunkManager $level, Vector3 $pos, Block $state, Random $random) : void {
		$i = $this->minSize + $random->nextBoundedInt($random->nextBoundedInt($this->extraSize + 1) + 1);

		for($j = 0; $j < $i; ++$j) {
			$level->setBlockAt($pos->x, $pos->y, $pos->z, $state);
			$pos = $pos->up();
		}
	}
}
