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
use pocketmine\level\generator\vanilla\block\utils\TreeGrower;
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\utils\Random;

class Sapling extends Flowable implements Growable {
	use StaticSupportTrait;

	public const OAK = 0;
	public const SPRUCE = 1;
	public const BIRCH = 2;
	public const JUNGLE = 3;
	public const ACACIA = 4;
	public const DARK_OAK = 5;

	protected $id = self::SAPLING;

	public function __construct(int $meta = 0){
		$this->meta = $meta;
	}

	public function getName() : string
	{
		static $names = [
			0 => "Oak Sapling",
			1 => "Spruce Sapling",
			2 => "Birch Sapling",
			3 => "Jungle Sapling",
			4 => "Acacia Sapling",
			5 => "Dark Oak Sapling"
		];
		return $names[$this->getVariant()] ?? "Unknown";
	}

	public function getTreeGrower() : ?TreeGrower {
		static $types = [
			0 => TreeGrower::OAK(),
			1 => TreeGrower::SPRUCE(),
			2 => TreeGrower::BIRCH(),
			3 => TreeGrower::JUNGLE(),
			4 => TreeGrower::ACACIA(),
			5 => TreeGrower::DARK_OAK()
		];
		return $types[$this->getVariant()] ?? null;
	}

	protected function canBeSupportedAt(Block $block) : bool{
		$supportBlock = $block->getSide(Facing::DOWN);
		return
			$supportBlock instanceof Grass ||
			$supportBlock instanceof Dirt ||
			$supportBlock instanceof Mycelium ||
			$supportBlock instanceof Podzol ||
			$supportBlock instanceof Farmland ||
			$supportBlock instanceof Mud;
	}

	public function ticksRandomly() : bool{
		return true;
	}

	public function onRandomTick() : void{
		$random = $this->level->random;
		if ($this->level->getFullLightAt($this->x, $this->y, $this->z) >= 8 && $random->nextBoundedInt(7) === 0) {
			$this->grow($random, null);
		}
	}

	public function canGrow(Random $random, ?Player $player) : bool{
		return $this->getTreeGrower() !== null;
	}

	public function canUseBonemeal(Random $random, ?Player $player) : bool{
		return $random->nextFloat() < 0.45;
	}

	public function grow(Random $random, ?Player $player) : void {
		if (BlockEventHelper::grow($this, $this, $player)) {
			if ($this->isReady()) {
				$this->getTreeGrower()->growTree($this->getLevel(), $this, $this, $random);
			} else {
				$this->setReady(true);
				$this->getLevel()->setBlock($this, $this, true);
			}
		}
	}

	public function getVariantBitmask() : int
	{
		return 0x07;
	}

	public function getReadyBitmask() : int {
		return 0x08;
	}

	public function isReady() : bool {
		return ($this->meta & $this->getReadyBitmask()) !== 0;
	}

	public function setReady(bool $value) : void {
		$this->meta = ($this->meta & ~$this->getReadyBitmask()) | ($value ? $this->getReadyBitmask() : 0);
	}

	public function getFuelTime() : int{
		return 100;
	}
}
