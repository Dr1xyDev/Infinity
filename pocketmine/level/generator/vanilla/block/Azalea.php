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

class Azalea extends Flowable implements Growable {
	use StaticSupportTrait;

	protected $id = self::AZALEA;

	public function __construct(int $meta = 0){
		$this->meta = $meta;
	}

	public function getName() : string{
		return "Azalea";
	}

	public function canGrow(Random $random, ?Player $player) : bool{
		return true;
	}

	public function canUseBonemeal(Random $random, ?Player $player) : bool{
		return $random->nextFloat() < 0.45;
	}

	public function grow(Random $random, ?Player $player) : void {
		if (BlockEventHelper::grow($this, $this, $player)) {
			TreeGrower::AZALEA()->growTree($this->getLevel(), $this, $this, $random);
		}
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
}
