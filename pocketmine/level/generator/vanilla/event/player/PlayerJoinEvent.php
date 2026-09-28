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

use pocketmine\level\generator\vanilla\lang\TextContainer;
use pocketmine\Player;

/**
 * Called when the player spawns in the world after logging in, when they first see the terrain.
 *
 * Note: A lot of data is sent to the player between login and this event. Disconnecting the player during this event
 * will cause this data to be wasted. Prefer disconnecting at login-time if possible to minimize bandwidth wastage.
 * @see PlayerLoginEvent
 */
class PlayerJoinEvent extends PlayerEvent
{
	/** @var string|TextContainer */
	protected $joinMessage;

	/**
	 * PlayerJoinEvent constructor.
	 *
	 * @param TextContainer|string $joinMessage
	 */
	public function __construct(Player $player, $joinMessage)
	{
		$this->player = $player;
		$this->joinMessage = $joinMessage;
	}

	/**
	 * @param string|TextContainer $joinMessage
	 */
	public function setJoinMessage($joinMessage) : void
	{
		$this->joinMessage = $joinMessage;
	}

	/**
	 * @return string|TextContainer
	 */
	public function getJoinMessage()
	{
		return $this->joinMessage;
	}
}
