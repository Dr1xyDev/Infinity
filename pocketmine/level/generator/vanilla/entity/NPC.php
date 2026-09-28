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

namespace pocketmine\level\generator\vanilla\entity;

use pocketmine\level\generator\vanilla\level\ChunkManager;
use pocketmine\level\generator\vanilla\level\Level;
use pocketmine\level\generator\vanilla\nbt\tag\ByteArrayTag;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\level\generator\vanilla\nbt\tag\DoubleTag;
use pocketmine\level\generator\vanilla\nbt\tag\FloatTag;
use pocketmine\level\generator\vanilla\nbt\tag\ListTag;
use pocketmine\level\generator\vanilla\nbt\tag\StringTag;
use pocketmine\Player;

class NPC extends Human
{
	protected ?Skin $skin = null;

	/**
	 * NPC constructor.
	 */
	public function __construct(ChunkManager $level, float $x, float $y, float $z, float $yaw, float $pitch, Skin $skin)
	{
		$this->skin = $skin;

		$nbt = new CompoundTag("", [
			new ListTag("Pos", [
				new DoubleTag("", $x),
				new DoubleTag("", $y),
				new DoubleTag("", $z)
			]),
			new ListTag("Motion", [
				new DoubleTag("", 0.0),
				new DoubleTag("", 0.0),
				new DoubleTag("", 0.0)
			]),
			new ListTag("Rotation", [
				new FloatTag("", $yaw),
				new FloatTag("", $pitch)
			]),
			new CompoundTag("Skin", [
				new ByteArrayTag("Data", $skin->getSkinData()),
				new StringTag("Name", $skin->getSkinId())
			])
		]);

		parent::__construct($level, $nbt);
		$this->setSkin($skin);
		$this->sendSkin();
		$this->spawnToAll();
	}

	/**
	 * Spawns the NPC to all players in the level
	 */
	public function spawnToAll() : void
	{
		foreach ($this->getLevel()->getPlayers() as $player) {
			$this->spawnTo($player);
		}
	}

	/**
	 * Spawns the NPC to a specific player
	 */
	public function spawnTo(Player $player) : void
	{
		parent::spawnTo($player);
	}

	/**
	 * Despawns the NPC from all players in the level
	 */
	public function despawnFromAll() : void
	{
		foreach ($this->getLevel()->getPlayers() as $player) {
			$this->despawnFrom($player, true);
		}
	}

	/**
	 * Despawns the NPC from a specific player
	 */
	public function despawnFrom(Player $player, bool $send = true) : void
	{
		parent::despawnFrom($player, $send);
	}
}
