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

use pocketmine\Player;

/**
 * A container which is filled from a vanilla loot table the first time it is used (opened, broken, emptied by a hopper),
 * like the chests of generated structures. The table is stored in the "LootTable" and "LootTableSeed" tags.
 */
interface LootContainer extends Container
{
	public const TAG_LOOT_TABLE = "LootTable";
	public const TAG_LOOT_TABLE_SEED = "LootTableSeed";

	public function getLootTable() : ?string;

	/**
	 * @param int $seed 0 for a random loot
	 */
	public function setLootTable(?string $lootTable, int $seed = 0) : void;

	/**
	 * Generates the loot into the inventory if it wasn't done yet
	 */
	public function unpackLootTable(?Player $player = null) : void;
}
