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

namespace pocketmine\level\generator\vanilla\level\generator\structure;

use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;

/**
 * What structure pieces are built into. Coordinates are world coordinates.
 */
interface StructureWorld
{
	/**
	 * Full block (id << Block::INTERNAL_METADATA_BITS | meta), air outside of the writable area
	 */
	public function getFullBlock(int $x, int $y, int $z) : int;

	public function setFullBlock(int $x, int $y, int $z, int $fullBlock) : void;

	/**
	 * Whether the sky can be seen from this block (replacement for the light level while generating)
	 */
	public function isSkyVisible(int $x, int $y, int $z) : bool;

	public function addTile(CompoundTag $nbt) : void;

	public function addEntity(CompoundTag $nbt) : void;
}
