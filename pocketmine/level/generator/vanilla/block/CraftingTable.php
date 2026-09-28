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

use pocketmine\level\generator\vanilla\inventory\CraftingGrid;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\WindowTypes;
use pocketmine\Player;

class CraftingTable extends Solid
{
	protected $id = self::CRAFTING_TABLE;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getHardness() : float
	{
		return 2.5;
	}

	public function getName() : string
	{
		return "Crafting Table";
	}

	public function getToolType() : int
	{
		return BlockToolType::TYPE_AXE;
	}

	public function onActivate(Item $item, ?Player $player = null) : bool
	{
		if ($player instanceof Player) {
			$player->setCraftingGrid(new CraftingGrid($player, CraftingGrid::SIZE_BIG));

			if ($player->getProtocolVersion() >= ProtocolInfo::PROTOCOL_407) {
				$pk = new ContainerOpenPacket();
				$pk->windowId = $player->getNewWindowId();
				$pk->type = WindowTypes::WORKBENCH;
				$pk->x = $this->getFloorX();
				$pk->y = $this->getFloorY();
				$pk->z = $this->getFloorZ();
				$player->sendDataPacket($pk);

				$player->setCurrentWindowType(WindowTypes::WORKBENCH);
			}
		}

		return true;
	}

	public function getFuelTime() : int
	{
		return 300;
	}
}
