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

namespace pocketmine\level\generator\vanilla\network\mcpe\convert;

use pocketmine\level\generator\vanilla\entity\player\ItemStackContainerIdTranslator;
use pocketmine\level\generator\vanilla\inventory\Inventory;
use pocketmine\level\generator\vanilla\item\Durable;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\ContainerUIIds;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\FullContainerName;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\stackresponse\ItemStackResponse;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\stackresponse\ItemStackResponseContainerInfo;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\stackresponse\ItemStackResponseSlotInfo;
use pocketmine\Player;

final class ItemStackResponseBuilder
{
	/**
	 * @var int[][]
	 * @phpstan-var array<int, array<int, int>>
	 */
	private array $changedSlots = [];

	public function __construct(
		private int $requestId,
		private Player $player
	) {
	}

	public function addSlot(int $containerInterfaceId, int $slotId) : void
	{
		$this->changedSlots[$containerInterfaceId][$slotId] = $slotId;
	}

	/**
	 * @phpstan-return array{Inventory, int}
	 */
	private function getInventoryAndSlot(int $containerInterfaceId, int $slotId) : ?array
	{
		[$windowId, $slotId] = ItemStackContainerIdTranslator::translate($containerInterfaceId, $this->player->getCurrentWindowId(), $slotId);
		$windowAndSlot = $this->player->locateWindowAndSlot($windowId, $slotId);
		if ($windowAndSlot === null) {
			return null;
		}
		[$inventory, $slot] = $windowAndSlot;
		if (!$inventory->slotExists($slot)) {
			return null;
		}

		return [$inventory, $slot];
	}

	public function build() : ItemStackResponse
	{
		$responseInfosByContainer = [];
		foreach ($this->changedSlots as $containerInterfaceId => $slotIds) {
			if ($containerInterfaceId === ContainerUIIds::CREATED_OUTPUT) {
				continue;
			}
			foreach ($slotIds as $slotId) {
				$inventoryAndSlot = $this->getInventoryAndSlot($containerInterfaceId, $slotId);
				if ($inventoryAndSlot === null) {
					//a plugin may have closed the inventory during an event, or the slot may have been invalid
					continue;
				}
				[$inventory, $slot] = $inventoryAndSlot;

				$item = $inventory->getItem($slot);

				$responseInfosByContainer[$containerInterfaceId][] = new ItemStackResponseSlotInfo(
					$slotId,
					$slotId,
					$item->getCount(),
					$item->isNull() ? 0 : 1,
					$item->getCustomName(),
					$item->getCustomName(),
					$item instanceof Durable ? $item->getDamage() : 0,
				);
			}
		}

		$responseContainerInfos = [];
		foreach ($responseInfosByContainer as $containerInterfaceId => $responseInfos) {
			$responseContainerInfos[] = new ItemStackResponseContainerInfo(new FullContainerName($containerInterfaceId), $responseInfos);
		}

		return new ItemStackResponse(ItemStackResponse::RESULT_OK, $this->requestId, $responseContainerInfos);
	}
}
