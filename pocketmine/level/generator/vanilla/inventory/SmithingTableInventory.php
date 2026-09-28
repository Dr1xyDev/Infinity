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

namespace pocketmine\level\generator\vanilla\inventory;

use pocketmine\level\generator\vanilla\block\SmithingTable;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\UIInventorySlotOffset;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\WindowTypes;
use pocketmine\Player;

class SmithingTableInventory extends ContainerInventory implements FakeInventory, FakeResultInventory
{
	public const int SLOT_INPUT = 0;
	public const int SLOT_MATERIAL = 1;
	public const int SLOT_TEMPLATE = 2;

	public function __construct(SmithingTable $tile)
	{
		parent::__construct($tile);
	}

	public function getName() : string
	{
		return "Smithing Table";
	}

	public function getDefaultSize() : int
	{
		return 3;
	}

	public function getUIOffsets(?Player $player) : array
	{
		return UIInventorySlotOffset::SMITHING_TABLE;
	}

	public function onResult(Player $player, Item $result) : bool
	{
		return true; //TODO:
	}

	public function getNetworkType() : int
	{
		return WindowTypes::SMITHING_TABLE;
	}

	/**
	 * @param Player|Player[] $target
	 */
	public function sendContents($target) : void{
		if ($target instanceof Player) {
			$target = [$target];
		}

		foreach ($target as $player) {
			if ($player->getProtocolVersion() < ProtocolInfo::PROTOCOL_407) {
				continue;
			}
			parent::sendContents($player);
		}
	}

	/**
	 * @param Player|Player[] $target
	 */
	public function sendSlot(int $index, $target) : void
	{
		if ($target instanceof Player) {
			$target = [$target];
		}

		foreach ($target as $player) {
			if ($player->getProtocolVersion() < ProtocolInfo::PROTOCOL_407) {
				continue;
			}
			parent::sendSlot($index, $player);
		}
	}
}
