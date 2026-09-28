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

namespace pocketmine\level\generator\vanilla\event\player\cheat;

use pocketmine\level\generator\vanilla\event\Cancellable;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\Player;

/**
 * Called when a player attempts to perform movement cheats such as clipping through blocks.
 */
class PlayerIllegalMoveEvent extends PlayerCheatEvent implements Cancellable{

	private Vector3 $attemptedPosition;
	private Vector3 $originalPosition;
	private Vector3 $expectedPosition;

	public function __construct(Player $player, Vector3 $attemptedPosition, Vector3 $originalPosition){
		$this->player = $player;
		$this->attemptedPosition = $attemptedPosition;
		$this->originalPosition = $originalPosition;
		$this->expectedPosition = $player->asVector3();
	}

	/**
	 * Returns the position the player attempted to move to.
	 */
	public function getAttemptedPosition() : Vector3{
		return $this->attemptedPosition;
	}

	public function getOriginalPosition() : Vector3{
		return $this->originalPosition;
	}

	public function getExpectedPosition() : Vector3{
		return $this->expectedPosition;
	}
}
