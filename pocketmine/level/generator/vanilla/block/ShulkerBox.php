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

use pocketmine\level\generator\vanilla\block\utils\ColorBlockMetaHelper;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\tile\ShulkerBox as TileShulkerBox;
use pocketmine\level\generator\vanilla\tile\Tile;

class ShulkerBox extends Transparent
{
	protected $id = self::SHULKER_BOX;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getHardness() : float
	{
		return 6;
	}

	public function getName() : string
	{
		return ColorBlockMetaHelper::getColorFromMeta($this->getVariant()) . " Shulker Box";
	}

	public function getToolType() : int
	{
		return BlockToolType::TYPE_PICKAXE;
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool
	{
		if (parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player)) {

			Tile::createTile(Tile::SHULKER_BOX, $this->getLevel(), TileShulkerBox::createNBT($this, $face, $item, $player));

			return true;
		}
		return false;
	}

	public function onActivate(Item $item, ?Player $player = null) : bool
	{
		if ($player instanceof Player) {
			$tile = $this->getLevel()->getTile($this);
			if ($tile instanceof TileShulkerBox) {
				$player->addWindow($tile->getInventory());
			}
		}

		return true;
	}

	public function isAffectedBySilkTouch() : bool
	{
		return false;
	}

	public function getDropsForCompatibleTool(Item $item) : array
	{
		$tile = $this->getLevel()->getTile($this);
		if ($tile instanceof TileShulkerBox) {
			$drop = $this->asItem();
			$shulkerNBT = $tile->getCleanedNBT();
			if($shulkerNBT !== null){
				$drop->setNamedTag($shulkerNBT);
			}
			if($tile->hasName()){
				$drop->setCustomName($tile->getName());
			}

			return [$drop];
		}

		return [];
	}

	public function onBreak(Item $item, ?Player $player = null) : bool
	{
		$tile = $this->level->getTile($this);
		if ($tile instanceof TileShulkerBox) {
			$tile->getInventory()->clearAll(false);
		}
		return parent::onBreak($item, $player);
	}
}
