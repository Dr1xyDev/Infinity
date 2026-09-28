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

use pocketmine\level\generator\vanilla\item\Hoe;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\Potion;
use pocketmine\level\generator\vanilla\item\SplashPotion;
use pocketmine\level\generator\vanilla\level\sound\ItemUseOnBlockSound;
use pocketmine\level\generator\vanilla\level\sound\WaterSplashSound;
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\Player;

class Dirt extends Solid
{
	public const TYPE_NORMAL = 0;
	public const TYPE_COARSE = 1;

	protected $id = self::DIRT;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getHardness() : float
	{
		return 0.5;
	}

	public function getToolType() : int
	{
		return BlockToolType::TYPE_SHOVEL;
	}

	public function getName() : string
	{
		if ($this->meta === self::TYPE_COARSE) {
			return "Coarse Dirt";
		}
		return "Dirt";
	}

	public function onActivate(Item $item, ?Player $player = null) : bool
	{
		if ($item instanceof Hoe) {
			$up = $this->getSide(Facing::UP);
			if ($up->getId() !== BlockIds::AIR) {
				return true;
			}

			$item->applyDamage(1);

			$newBlock = $this->meta === Dirt::TYPE_NORMAL ? BlockFactory::get(Block::FARMLAND) : BlockFactory::get(Block::DIRT);
			$center = $this->add(0.5, 0.5, 0.5);
			$this->level->addSound(new ItemUseOnBlockSound($center, $newBlock));
			$this->level->setBlock($this, $newBlock);
			return true;
			//TODO: bonemeal particles, growth sounds
		} elseif (($item instanceof Potion || $item instanceof SplashPotion) && $item->getDamage() === Potion::WATER) {
			$item->pop();
			$this->level->setBlock($this, BlockFactory::get(Block::MUD));
			$this->level->addSound(new WaterSplashSound($this, 0.5));
			return true;
		}

		return false;
	}
}
