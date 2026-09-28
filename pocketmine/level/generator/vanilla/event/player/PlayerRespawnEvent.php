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

use InvalidArgumentException;
use pocketmine\level\generator\vanilla\level\Position;
use pocketmine\Player;

/**
 * Called when a player is respawned
 */
class PlayerRespawnEvent extends PlayerEvent
{
	/** @var Position */
	protected $position;

	public function __construct(Player $player, Position $position)
	{
		$this->player = $player;
		$this->position = $position;
	}

	public function getRespawnPosition() : Position
	{
		return $this->position;
	}

	public function setRespawnPosition(Position $position) : void
	{
		if (!$position->isValid()) {
			throw new InvalidArgumentException("Spawn position must reference a valid and loaded World");
		}
		$this->position = $position;
	}
}
