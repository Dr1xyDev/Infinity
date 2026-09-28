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
use pocketmine\level\generator\vanilla\block\utils\StaticSupportTrait;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\Player;

use pocketmine\level\generator\vanilla\utils\Random;
use function min;

abstract class Crops extends Flowable implements Growable
{
	use StaticSupportTrait;

	public function getMaxAge() : int {
		return 7;
	}

	public function getAge() : int {
		return $this->meta;
	}

	public function setAge(int $value) : self {
		$this->meta = $value;
		return $this;
	}

	public function isMaxAge() : bool {
		return $this->meta >= $this->getMaxAge();
	}

	protected function canBeSupportedAt(Block $block) : bool{
		return $block->getSide(Facing::DOWN)->getId() === BlockIds::FARMLAND;
	}

	public function ticksRandomly() : bool{
		return !$this->isMaxAge();
	}

	public function onRandomTick() : void{
		$level = $this->level;

		$lightAbove = $level->getFullLightAt($this->x, $this->y + 1, $this->z);
		if ($lightAbove >= 9) {
			if (!$this->isMaxAge()) {
				if ($level->random->nextBoundedInt((int) (25.0 / $this->getGrowthChance()) + 1) === 0) {
					$newState = clone $this;
					$newState->meta += 1;
					BlockEventHelper::grow($this, $newState, null);
				}
			}
		}
	}

	protected function getBonemealAgeIncrease(Random $random) : int {
		return 2 >= 5 ? 2 : $random->nextBoundedInt(4) + 2;
	}

	protected function getGrowthChance() : float {
		$growthChance = 1.0;

		$soilPos = $this->getSide(Facing::DOWN);
		for ($dx = -1; $dx <= 1; ++$dx) {
			for ($dz = -1; $dz <= 1; ++$dz) {
				$soilBonus = 0.0;

				$soilBlock = $this->level->getBlock($soilPos->add($dx, 0, $dz));

				if ($soilBlock instanceof Farmland) {
					$soilBonus = 1.0;
					if ($soilBlock->getMoisture() > 0) {
						$soilBonus = 3.0;
					}
				}

				if ($dx !== 0 || $dz !== 0) {
					$soilBonus /= 4.0;
				}

				$growthChance += $soilBonus;
			}
		}

		$north = $this->getSide(Facing::NORTH);
		$south = $this->getSide(Facing::SOUTH);
		$west = $this->getSide(Facing::WEST);
		$east = $this->getSide(Facing::EAST);

		$sameCropOnWestEast = ($this->getId() === $west->getId()) || ($this->getId() === $east->getId());
		$sameCropOnNorthSouth = ($this->getId() === $north->getId()) || ($this->getId() === $south->getId());

		if ($sameCropOnWestEast && $sameCropOnNorthSouth) {
			$growthChance /= 2.0;
		} else {
			$sameCropOnDiagonal =
				$this->getId() === $west->getSide(Facing::NORTH)->getId() ||
				$this->getId() === $east->getSide(Facing::NORTH)->getId() ||
				$this->getId() === $east->getSide(Facing::SOUTH)->getId() ||
				$this->getId() === $west->getSide(Facing::SOUTH)->getId();

			if ($sameCropOnDiagonal) {
				$growthChance /= 2.0;
			}
		}

		return $growthChance;
	}

	public function canGrow(Random $random, ?Player $player) : bool{
		return !$this->isMaxAge();
	}

	public function canUseBonemeal(Random $random, ?Player $player) : bool{
		return true;
	}

	public function grow(Random $random, ?Player $player) : void {
		$newState = clone $this;
		$newState->meta = min($this->getMaxAge(), $this->getAge() + $this->getBonemealAgeIncrease($random));
		BlockEventHelper::grow($this, $newState, $player);
	}

	abstract public function getSeed() : Item;

	abstract public function getCrop() : Item;

	public function isAffectedBySilkTouch() : bool{
		return false;
	}

	public function getPickedItem(bool $addUserData = false) : Item{
		return $this->getSeed();
	}
}
