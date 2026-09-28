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
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\utils\Random;

class Sugarcane extends Flowable implements Growable
{
	use StaticSupportTrait {
		onNearbyBlockChange as onSupportBlockChange;
	}

	public const MAX_AGE = 15;

	protected $id = self::SUGARCANE_BLOCK;

	protected $itemId = Item::SUGARCANE;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getName() : string
	{
		return "Sugarcane";
	}

	public function getVariantBitmask() : int
	{
		return 0;
	}

	protected function canBeSupportedAt(Block $block) : bool{
		$supportBlock = $block->getSide(Facing::DOWN);
		return $supportBlock->isSameType($this) ||
			$supportBlock instanceof Grass ||
			$supportBlock instanceof Dirt ||
			$supportBlock instanceof Mycelium ||
			$supportBlock instanceof Podzol ||
			$supportBlock instanceof Mud ||
			$supportBlock instanceof Sand;
	}

	public function canGrow(Random $random, ?Player $player = null) : bool {
		$current = $this;
		$height = 1;

		$down = $current->getSide(Facing::DOWN);
		while($down->isSameType($this)){
			$height++;
			$down = $down->getSide(Facing::DOWN);
		}

		$up = $current->getSide(Facing::UP);
		while($up->isSameType($this)){
			$height++;
			$current = $up;
			$up = $current->getSide(Facing::UP);
		}

		return $height < 3 && $current->getSide(Facing::UP)->getId() === BlockIds::AIR;
	}

	public function canUseBonemeal(Random $random, ?Player $player = null) : bool {
		return true;
	}

	public function grow(Random $random, ?Player $player = null) : void{
		$current = $this;

		while (($up = $current->getSide(Facing::UP))->isSameType($this)) {
			$current = $up;
		}

		$height = 0;
		$scan = $current;
		while ($scan->isSameType($this)) {
			$height++;
			$scan = $scan->getSide(Facing::DOWN);
		}

		while ($height < 3) {
			$up = $current->getSide(Facing::UP);
			if ($up->getId() === BlockIds::AIR) {
				BlockEventHelper::grow($up, BlockFactory::get(BlockIds::SUGARCANE_BLOCK), $player);
				$current = $up;
				$height++;
			} else {
				break;
			}
		}
	}

	public function ticksRandomly() : bool
	{
		return true;
	}

	public function onRandomTick() : void{
		$down = $this->getSide(Facing::DOWN);
		if(!$down->isSameType($this)){
			if(!$this->hasNearbyWater($down)){
				$this->level->useBreakOn($this, createParticles: true);
				return;
			}

			if($this->meta === self::MAX_AGE){
				$current = $this;
				while(($up = $current->getSide(Facing::UP))->isSameType($this)){
					$current = $up;
				}

				$height = 0;
				$scan = $current;
				while($scan->isSameType($this)){
					$height++;
					$scan = $scan->getSide(Facing::DOWN);
				}

				if($height < 3 && $current->getSide(Facing::UP)->getId() === BlockIds::AIR){
					BlockEventHelper::grow($current->getSide(Facing::UP), BlockFactory::get(BlockIds::SUGARCANE_BLOCK), null);
				}
				$this->meta = 0;
			}else{
				++$this->meta;
			}

			$this->level->setBlock($this, $this);
		}
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool
	{
		$down = $blockReplace->getSide(Facing::DOWN);
		if($down->isSameType($this)){
			return parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
		}

		if($this->canBeSupportedAt($blockReplace) && $this->hasNearbyWater($down)){
			return parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
		}

		return false;
	}

	private function hasNearbyWater(Block $down) : bool{
		foreach($down->getHorizontalSides() as $sideBlock){
			if($sideBlock instanceof Water || $sideBlock instanceof FrostedIce){
				return true;
			}
		}
		return false;
	}

	public function onNearbyBlockChange() : void{
		$down = $this->getSide(Facing::DOWN);
		if(!$down->isSameType($this) && !$this->hasNearbyWater($down)){
			$this->level->useBreakOn($this, createParticles: true);
		}else{
			$this->onSupportBlockChange();
		}
	}
}
