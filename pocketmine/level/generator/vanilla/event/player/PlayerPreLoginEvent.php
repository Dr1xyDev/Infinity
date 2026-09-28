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

use pocketmine\level\generator\vanilla\event\Cancellable;
use pocketmine\Player;

/**
 * Called when a player connects to the server, prior to authentication taking place.
 * Cancelling this event will cause the player to be disconnected with the kick message set.
 *
 * This event should be used to decide if the player may continue to login to the server. Do things like checking
 * bans, whitelisting, server-full etc here.
 *
 * WARNING: Any information about the player CANNOT be trusted at this stage, because they are not authenticated and
 * could be a hacker posing as another player.
 *
 * WARNING: Due to internal bad architecture, the player is not fully constructed at this stage, and errors might occur
 * when calling API methods on the player. Tread with caution.
 */
class PlayerPreLoginEvent extends PlayerEvent implements Cancellable
{
	/** @var string */
	protected $kickMessage;

	public function __construct(Player $player, string $kickMessage)
	{
		$this->player = $player;
		$this->kickMessage = $kickMessage;
	}

	public function setKickMessage(string $kickMessage) : void
	{
		$this->kickMessage = $kickMessage;
	}

	public function getKickMessage() : string
	{
		return $this->kickMessage;
	}
}
