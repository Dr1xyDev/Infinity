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
use pocketmine\Player;

class InfestedStone extends Solid
{
	protected $id = self::MONSTER_EGG;

	public const STONE_MONSTER_EGG = 0;
	public const COBBLESTONE_MONSTER_EGG = 1;
	public const STONE_BRICK_MONSTER_EGG = 2;
	public const MOSSY_STONE_BRICK_MONSTER_EGG = 3;
	public const CRACKED_STONE_BRICK_MONSTER_EGG = 4;
	public const CHISELED_STONE_BRICK_MONSTER_EGG = 5;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getName() : string
	{
		switch ($this->meta) {
			case self::STONE_MONSTER_EGG:
				return "Stone Monster Egg";
			case self::COBBLESTONE_MONSTER_EGG:
				return "Cobblestone Monster Egg";
			case self::STONE_BRICK_MONSTER_EGG:
				return "Stone Brick Monster Egg";
			case self::MOSSY_STONE_BRICK_MONSTER_EGG:
				return "Mossy Stone Brick Monster Egg";
			case self::CRACKED_STONE_BRICK_MONSTER_EGG:
				return "Cracked Stone Brick Monster Egg";
			case self::CHISELED_STONE_BRICK_MONSTER_EGG:
				return "Chiseled Stone Brick Monster Egg";
		}

		return "Infested Block";
	}

	public function getHardness() : float
	{
		return 0.75;
	}

	public function getDropsForCompatibleTool(Item $item) : array
	{
		return [];
	}

	public function onBreak(Item $item, ?Player $player = null) : bool
	{
		// TODO: Spawn silverfish

		return parent::onBreak($item, $player);
	}
}
