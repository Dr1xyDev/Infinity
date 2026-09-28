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

namespace pocketmine\level\generator\vanilla\level\format;

use pocketmine\world\format\PalettedBlockArray;

interface SubChunkInterface
{
	public function isEmpty(bool $checkLight = true) : bool;

	public function getEmptyBlockId() : int;

	public function getBlockId(int $x, int $y, int $z) : int;

	public function setBlockId(int $x, int $y, int $z, int $id) : bool;

	public function getBlockData(int $x, int $y, int $z) : int;

	public function setBlockData(int $x, int $y, int $z, int $data) : bool;

	public function getFullBlock(int $x, int $y, int $z) : int;

	public function setFullBlock(int $x, int $y, int $z, int $block) : bool;

	public function setBlock(int $x, int $y, int $z, ?int $id = null, ?int $data = null) : bool;

	public function getBlockLight(int $x, int $y, int $z) : int;

	public function setBlockLight(int $x, int $y, int $z, int $level) : bool;

	public function getBlockSkyLight(int $x, int $y, int $z) : int;

	public function setBlockSkyLight(int $x, int $y, int $z, int $level) : bool;

	public function getHighestBlockAt(int $x, int $z) : int;

	public function getBlockLightColumn(int $x, int $z) : string;

	public function getBlockSkyLightColumn(int $x, int $z) : string;

	/**
	 * @return PalettedBlockArray[]
	 */
	public function getBlockLayers() : array;

	public function getBlockSkyLightArray() : string;

	public function setBlockSkyLightArray(string $data);

	public function getBlockLightArray() : string;

	public function setBlockLightArray(string $data);
}
