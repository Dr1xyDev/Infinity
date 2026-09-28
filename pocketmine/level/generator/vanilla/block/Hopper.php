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
use pocketmine\level\generator\vanilla\item\TieredTool;
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\tile\Hopper as TileHopper;
use pocketmine\level\generator\vanilla\tile\Tile;

class Hopper extends Transparent
{
	protected $id = self::HOPPER_BLOCK;
	protected $itemId = Item::HOPPER;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getHardness() : float
	{
		return 3;
	}

	public function getBlastResistance() : float
	{
		return 24;
	}

	public function getToolType() : int
	{
		return BlockToolType::TYPE_PICKAXE;
	}

	public function getToolHarvestLevel() : int
	{
		return TieredTool::TIER_WOODEN;
	}

	public function getName() : string
	{
		return "Hopper";
	}

	public function getVariantBitmask() : int
	{
		return 0;
	}

	public function onActivate(Item $item, ?Player $player = null) : bool
	{
		if ($player instanceof Player) {
			$hopper = $this->getLevel()->getTile($this);
			if ($hopper instanceof TileHopper) {

				if (!$hopper->canOpenWith($item->getCustomName())) {
					return true;
				}

				$player->addWindow($hopper->getInventory());
			}
		}

		return true;
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool
	{
		static $faces = [
			0 => Facing::DOWN,
			1 => Facing::DOWN, // Not used
			2 => Facing::SOUTH,
			3 => Facing::NORTH,
			4 => Facing::EAST,
			5 => Facing::WEST
		];

		$this->meta = $faces[$face];
		$this->getLevel()->setBlock($this, $this, true, true);

		Tile::createTile(Tile::HOPPER, $this->getLevel(), TileHopper::createNBT($this, $face, $item, $player));

		return true;
	}

	public function onScheduledUpdate() : void{
		$level = $this->getLevel();
		$furnace = $level->getTile($this);
		if ($furnace instanceof TileHopper && $furnace->onUpdate()) {
			$level->scheduleDelayedBlockUpdate($this, 1); //TODO: check this
		}
	}
}
