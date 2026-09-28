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
use pocketmine\level\generator\vanilla\block\StoneSlab;

class DesertWellsFeature extends Feature {

	public function place(FeaturePlaceContext $context) : bool
	{
		$pos = $context->origin();
		$level = $context->level();

		$pos = $pos->up();
		while($pos->y > 2 && $level->getBlockAt($pos->x, $pos->y, $pos->z)->getId() === BlockIds::AIR){
			$pos = $pos->down();
		}

		if ($level->getBlockAt($pos->x, $pos->y, $pos->z)->getId() !== BlockIds::SAND) {
			return false;
		}

		for($i = -2; $i <= 2; ++$i) {
			for($j = -2; $j <= 2; ++$j) {
				$b1 = $level->getBlockAt($pos->x + $i, $pos->y - 1, $pos->z + $j)->getId();
				$b2 = $level->getBlockAt($pos->x + $i, $pos->y - 2, $pos->z + $j)->getId();
				if ($b1 === BlockIds::AIR && $b2 === BlockIds::AIR) {
					return false;
				}
			}
		}

		$sandstone = BlockFactory::get(BlockIds::SANDSTONE);
		$sandSlab = BlockFactory::get(BlockIds::STONE_SLAB, StoneSlab::SANDSTONE);
		$water = BlockFactory::get(BlockIds::WATER);

		for($l = -1; $l <= 0; ++$l) {
			for($l1 = -2; $l1 <= 2; ++$l1) {
				for($k = -2; $k <= 2; ++$k) {
					$this->setBlock($level, $pos->add($l1, $l, $k), $sandstone);
				}
			}
		}

		$this->setBlock($level, $pos, $water);
		$this->setBlock($level, $pos->north(), $water);
		$this->setBlock($level, $pos->south(), $water);
		$this->setBlock($level, $pos->east(), $water);
		$this->setBlock($level, $pos->west(), $water);

		for($i1 = -2; $i1 <= 2; ++$i1) {
			for($i2 = -2; $i2 <= 2; ++$i2) {
				if ($i1 === -2 || $i1 === 2 || $i2 === -2 || $i2 === 2) {
					$this->setBlock($level, $pos->add($i1, 1, $i2), $sandstone);
				}
			}
		}

		$this->setBlock($level, $pos->add(2, 1, 0), $sandSlab);
		$this->setBlock($level, $pos->add(-2, 1, 0), $sandSlab);
		$this->setBlock($level, $pos->add(0, 1, 2), $sandSlab);
		$this->setBlock($level, $pos->add(0, 1, -2), $sandSlab);

		for($j1 = -1; $j1 <= 1; ++$j1) {
			for($j2 = -1; $j2 <= 1; ++$j2) {
				if ($j1 === 0 && $j2 === 0) {
					$this->setBlock($level, $pos->add($j1, 4, $j2), $sandstone);
				} else {
					$this->setBlock($level, $pos->add($j1, 4, $j2), $sandSlab);
				}
			}
		}

		for($k1 = 1; $k1 <= 3; ++$k1) {
			$this->setBlock($level, $pos->add(-1, $k1, -1), $sandstone);
			$this->setBlock($level, $pos->add(-1, $k1, 1), $sandstone);
			$this->setBlock($level, $pos->add(1, $k1, -1), $sandstone);
			$this->setBlock($level, $pos->add(1, $k1, 1), $sandstone);
		}

		return true;
	}
}
