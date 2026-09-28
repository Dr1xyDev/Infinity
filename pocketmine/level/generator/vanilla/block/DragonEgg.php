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

use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\level\Level;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\LevelEventPacket;
use pocketmine\Player;
use function abs;

class DragonEgg extends Fallable
{
	protected $id = self::DRAGON_EGG;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getName() : string
	{
		return "Dragon Egg";
	}

	public function getHardness() : float
	{
		return 3;
	}

	public function getBlastResistance() : float
	{
		return 45;
	}

	public function getLightLevel() : int
	{
		return 1;
	}

	public function isTransparent() : bool
	{
		return true;
	}

	public function isBreakable(Item $item) : bool
	{
		return false;
	}

	public function getToolType() : int
	{
		return BlockToolType::TYPE_PICKAXE;
	}

	public function onActivate(Item $item, ?Player $player = null) : bool
	{
		$this->teleport();
		return true;
	}

	public function onAttack(Item $item, int $face, ?Player $player = null) : bool
	{
		$this->teleport();
		return false;
	}

	public function teleport() : void
	{
		$level = $this->getLevel();
		for($i = 0; $i < 1000; $i++){
			$blockPos = $this->add(
				$level->random->nextBoundedInt(16) - $level->random->nextBoundedInt(16),
				$level->random->nextBoundedInt(8) - $level->random->nextBoundedInt(8),
				$level->random->nextBoundedInt(16) - $level->random->nextBoundedInt(16)
			);
			if($level->getBlock($blockPos)->getId() === BlockIds::AIR && $blockPos->getY() > Level::Y_MIN && $blockPos->getY() < Level::Y_MAX){
				$diffX = $this->getFloorX() - $blockPos->getFloorX();
		$diffY = $this->getFloorY() - $blockPos->getFloorY();
		$diffZ = $this->getFloorZ() - $blockPos->getFloorZ();
				$level->broadcastLevelEvent(
					$this,
					LevelEventPacket::EVENT_PARTICLE_DRAGON_EGG_TELEPORT,
					(((((abs($diffX) << 16) | (abs($diffY) << 8)) | abs($diffZ)) | (($diffX < 0 ? 1 : 0) << 24)) | (($diffY < 0 ? 1 : 0) << 25)) | (($diffZ < 0 ? 1 : 0) << 26)
				);
				$level->setBlock($this, BlockFactory::get(BlockIds::AIR), true, true);
				$level->setBlock($blockPos, $this, true, true);
				break;
			}
		}
	}
}
