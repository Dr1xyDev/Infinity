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

namespace pocketmine\level\generator\vanilla\event\player;

use pocketmine\Player;

/**
 * Called after a player is sent a chunk as part of their view radius.
 */
final class PlayerPostChunkSendEvent extends PlayerEvent
{
	public function __construct(
		Player $player,
		private int $chunkX,
		private int $chunkZ
	) {
		$this->player = $player;
	}

	public function getChunkX() : int
	{
		return $this->chunkX;
	}

	public function getChunkZ() : int
	{
		return $this->chunkZ;
	}
}
