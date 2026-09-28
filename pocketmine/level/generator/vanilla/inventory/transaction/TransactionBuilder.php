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

namespace pocketmine\level\generator\vanilla\inventory\transaction;

use pocketmine\level\generator\vanilla\inventory\Inventory;
use pocketmine\level\generator\vanilla\inventory\transaction\action\InventoryAction;

use function spl_object_id;

final class TransactionBuilder
{
	/** @var TransactionBuilderInventory[] */
	private array $inventories = [];

	/** @var InventoryAction[] */
	private array $extraActions = [];

	public function addAction(InventoryAction $action) : void
	{
		$this->extraActions[spl_object_id($action)] = $action;
	}

	public function getInventory(Inventory $inventory) : TransactionBuilderInventory
	{
		$id = spl_object_id($inventory);
		return $this->inventories[$id] ??= new TransactionBuilderInventory($inventory);
	}

	/**
	 * @return InventoryAction[]
	 */
	public function generateActions() : array
	{
		$actions = $this->extraActions;

		foreach ($this->inventories as $inventory) {
			foreach ($inventory->generateActions() as $action) {
				$actions[spl_object_id($action)] = $action;
			}
		}

		return $actions;
	}
}
