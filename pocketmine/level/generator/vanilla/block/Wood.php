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

use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\Player;

class Wood extends Log
{
	public const OAK = 0;
	public const SPRUCE = 1;
	public const BIRCH = 2;
	public const JUNGLE = 3;
	public const ACACIA = 4;
	public const DARK_OAK = 5;

	public const STRIPPED_OAK = 8;
	public const STRIPPED_SPRUCE = 9;
	public const STRIPPED_BIRCH = 10;
	public const STRIPPED_JUNGLE = 11;
	public const STRIPPED_ACACIA = 12;
	public const STRIPPED_DARK_OAK = 13;

	public function getName() : string
	{
		static $names = [
			self::OAK => "Oak Wood",
			self::SPRUCE => "Spruce Wood",
			self::BIRCH => "Birch Wood",
			self::JUNGLE => "Jungle Wood",
			self::ACACIA => "Acacia Wood",
			self::DARK_OAK => "Dark Oak Wood",
			self::STRIPPED_OAK => "Stripped Oak Wood",
			self::STRIPPED_SPRUCE => "Stripped Spruce Wood",
			self::STRIPPED_BIRCH => "Stripped Birch Wood",
			self::STRIPPED_JUNGLE => "Stripped Jungle Wood",
			self::STRIPPED_ACACIA => "Stripped Acacia Wood",
			self::STRIPPED_DARK_OAK => "Stripped Dark Oak Wood",
		];
		return $names[$this->getVariant()] ?? "Unknown";
	}

	public function getVariantBitmask() : int{
		return -1;
	}

	public static function getAxisFaces(int $meta) : array {
		return match ($meta) {
			self::OAK => [Facing::DOWN => self::OAK, Facing::NORTH => 35, Facing::WEST => 34],
			self::SPRUCE => [Facing::DOWN => self::SPRUCE, Facing::NORTH => 39, Facing::WEST => 38],
			self::BIRCH => [Facing::DOWN => self::BIRCH, Facing::NORTH => 25, Facing::WEST => 24],
			self::JUNGLE => [Facing::DOWN => self::JUNGLE, Facing::NORTH => 27, Facing::WEST => 26],
			self::ACACIA => [Facing::DOWN => self::ACACIA, Facing::NORTH => 37, Facing::WEST => 36],
			self::DARK_OAK => [Facing::DOWN => self::DARK_OAK, Facing::NORTH => 17, Facing::WEST => 16],

			self::STRIPPED_OAK => [Facing::DOWN => self::STRIPPED_OAK, Facing::NORTH => 31, Facing::WEST => 30],
			self::STRIPPED_SPRUCE => [Facing::DOWN => self::STRIPPED_SPRUCE, Facing::NORTH => 23, Facing::WEST => 22],
			self::STRIPPED_BIRCH => [Facing::DOWN => self::STRIPPED_BIRCH, Facing::NORTH => 29, Facing::WEST => 28],
			self::STRIPPED_JUNGLE => [Facing::DOWN => self::STRIPPED_JUNGLE, Facing::NORTH => 21, Facing::WEST => 20],
			self::STRIPPED_ACACIA => [Facing::DOWN => self::STRIPPED_ACACIA, Facing::NORTH => 19, Facing::WEST => 18],
			self::STRIPPED_DARK_OAK => [Facing::DOWN => self::STRIPPED_DARK_OAK, Facing::NORTH => 33, Facing::WEST => 32],

			default => [Facing::DOWN => $meta, Facing::NORTH => 0x02, Facing::WEST => 0x01],
		};
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		$this->meta = $this->getAxisFaces($this->meta)[$face & ~0x01];
		return Block::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}
}
