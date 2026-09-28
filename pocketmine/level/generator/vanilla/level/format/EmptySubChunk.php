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

use pocketmine\level\generator\vanilla\block\Block;
use function str_repeat;

class EmptySubChunk implements SubChunkInterface
{
	/** @var EmptySubChunk */
	private static $instance;

	public static function getInstance() : self
	{
		if (self::$instance === null) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function isEmpty(bool $checkLight = true) : bool
	{
		return true;
	}

	public function getEmptyBlockId() : int
	{
		return Block::AIR;
	}

	public function getBlockId(int $x, int $y, int $z) : int
	{
		return 0;
	}

	public function setBlockId(int $x, int $y, int $z, int $id) : bool
	{
		return false;
	}

	public function getBlockData(int $x, int $y, int $z) : int
	{
		return 0;
	}

	public function setBlockData(int $x, int $y, int $z, int $data) : bool
	{
		return false;
	}

	public function getFullBlock(int $x, int $y, int $z) : int
	{
		return 0;
	}

	public function setFullBlock(int $x, int $y, int $z, int $block) : bool
	{
		return false;
	}

	public function setBlock(int $x, int $y, int $z, ?int $id = null, ?int $data = null) : bool
	{
		return false;
	}

	public function getBlockLight(int $x, int $y, int $z) : int
	{
		return 0;
	}

	public function setBlockLight(int $x, int $y, int $z, int $level) : bool
	{
		return false;
	}

	public function getBlockSkyLight(int $x, int $y, int $z) : int
	{
		return 15;
	}

	public function setBlockSkyLight(int $x, int $y, int $z, int $level) : bool
	{
		return false;
	}

	public function getHighestBlockAt(int $x, int $z) : int
	{
		return -1;
	}

	public function getBlockLightColumn(int $x, int $z) : string
	{
		return "\x00\x00\x00\x00\x00\x00\x00\x00";
	}

	public function getBlockSkyLightColumn(int $x, int $z) : string
	{
		return "\xff\xff\xff\xff\xff\xff\xff\xff";
	}

	public function getBlockLayers() : array
	{
		return [];
	}

	public function getBlockLightArray() : string
	{
		return str_repeat("\x00", 2048);
	}

	public function setBlockLightArray(string $data)
	{

	}

	public function getBlockSkyLightArray() : string
	{
		return str_repeat("\xff", 2048);
	}

	public function setBlockSkyLightArray(string $data)
	{

	}
}
