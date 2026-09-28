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

namespace pocketmine\level\generator\vanilla\inventory\transaction\action;

use pocketmine\level\generator\vanilla\network\mcpe\cache\CreativeInventoryCache;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\Player;

/**
 * Represents an action causing a change in an inventory slot.
 */
class ContainerDropItemAction extends DropItemAction
{
	protected int $fails = 0;

	public function onPreExecute(Player $source) : bool
	{
		return true;
	}

	public function execute(Player $source) : void
	{
		if ($source->getCraftingGrid()->contains($this->targetItem)) {
			$source->getCraftingGrid()->removeItem($this->targetItem);
		} elseif ($source->isCreative()) {
			if (CreativeInventoryCache::getInstance()->getItemIndex($this->targetItem) === -1) {
				if (++$this->fails >= 5) {
					return;
				}

				$source->addInventoryTransactionActions($this);
				return;
			}
		} else {
			if (++$this->fails >= 5) {
				return;
			}

			$source->addInventoryTransactionActions($this);
			return;
		}

		if (!$source->dropItem($this->targetItem)) {
			$source->getCraftingGrid()->removeItem($this->targetItem);
			$source->getInventory()->addItem($this->targetItem);

			$source->getInventory()->sendHeldItem($source->getViewers());
			if ($source->getProtocolVersion() < ProtocolInfo::PROTOCOL_407) {
				$source->getInventory()->sendContents($source);
			} else {
				$source->getInventory()->sendHeldItem($source);
			}

			return;
		}

		$source->getInventory()->sendHeldItem($source);
		$source->getInventory()->sendHeldItem($source->getViewers());
	}
}
