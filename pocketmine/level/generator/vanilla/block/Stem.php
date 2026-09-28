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

use pocketmine\level\generator\vanilla\block\utils\BlockEventHelper;
use pocketmine\level\generator\vanilla\block\utils\FortuneDropHelper;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\math\Facing;

abstract class Stem extends Crops {

	public function ticksRandomly() : bool{
		return true;
	}

	public function onRandomTick() : void{
		$level = $this->level;

		$lightAbove = $level->getFullLightAt($this->x, $this->y + 1, $this->z);
		if ($lightAbove >= 9) {
			if ($level->random->nextBoundedInt((int) (25.0 / $this->getGrowthChance()) + 1) === 0) {
				if (!$this->isMaxAge()) {
					$newState = clone $this;
					$newState->meta += 1;
					BlockEventHelper::grow($this, $newState, null);
				} else {
					foreach (Facing::HORIZONTAL as $facing) {
						if ($this->getSide($facing)->getId() === $this->getCrop()->getId()) {
							return;
						}
					}

					$facing = Facing::HORIZONTAL[$level->random->nextBoundedInt(4)];
					$block = $this->getSide($facing);
					$down = $block->getSide(Facing::DOWN);
					if ($block->getId() === BlockIds::AIR && ($down instanceof Farmland || $down instanceof Dirt || $down instanceof Grass)){
						BlockEventHelper::grow($block, $this->getCrop()->getBlock(), null);

						$newState = clone $this;
						$newState->meta = ($facing << 3) | 7;
						BlockEventHelper::grow($this, $newState, null);
					}
				}
			}
		}
	}

	public function onNearbyBlockChange() : void{
		if (!$this->canBeSupportedAt($this)) {
			$this->level->useBreakOn($this);
		} else {
			$direction = ($this->meta >> 3) & 0x7;
			if ($direction !== 0) {
				$block = $this->getSide($direction);
				if ($block->getId() === BlockIds::AIR) {
					$this->meta = $this->meta & 0x7;
					$this->level->setBlock($this, $this);
				}
			}
		}
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return [
			$this->getSeed()->setCount(FortuneDropHelper::binomial(ItemFactory::air(), 0, chance: ($this->meta + 1) / 15))
		];
	}
}
