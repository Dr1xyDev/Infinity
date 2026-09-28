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

namespace pocketmine\level\generator\vanilla\tile;

use pocketmine\level\generator\vanilla\inventory\Inventory;

interface Container
{
	public const TAG_ITEMS = "Items";
	public const TAG_LOCK = "Lock";

	/**
	 * @return Inventory
	 */
	public function getInventory();

	/**
	 * @return Inventory
	 */
	public function getRealInventory();

	/**
	 * Returns whether this container can be opened by an item with the given custom name.
	 */
	public function canOpenWith(string $key) : bool;
}
