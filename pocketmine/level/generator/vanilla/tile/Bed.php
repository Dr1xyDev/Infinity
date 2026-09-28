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

use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\Player;

class Bed extends Spawnable
{
	public const TAG_COLOR = "color";
	/** @var int */
	private $color = 14; //default to old red

	public function getColor() : int
	{
		return $this->color;
	}

	/**
	 * @return void
	 */
	public function setColor(int $color)
	{
		$this->color = $color & 0xf;
		$this->onChanged();
	}

	protected function readSaveData(CompoundTag $nbt) : void
	{
		$this->color = $nbt->getByte(self::TAG_COLOR, 14, true);
	}

	protected function writeSaveData(CompoundTag $nbt) : void
	{
		$nbt->setByte(self::TAG_COLOR, $this->color);
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt, int $protocolVersion) : void
	{
		$nbt->setByte(self::TAG_COLOR, $this->color);
	}

	protected static function createAdditionalNBT(CompoundTag $nbt, Vector3 $pos, ?int $face = null, ?Item $item = null, ?Player $player = null) : void
	{
		if ($item !== null) {
			$nbt->setByte(self::TAG_COLOR, $item->getDamage());
		}
	}
}
