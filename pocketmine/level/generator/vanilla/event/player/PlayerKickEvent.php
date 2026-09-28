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
use pocketmine\level\generator\vanilla\lang\TextContainer;
use pocketmine\Player;

/**
 * Called when a player leaves the server
 */
class PlayerKickEvent extends PlayerEvent implements Cancellable
{
	/** @var TextContainer|string */
	protected $quitMessage;

	/** @var string */
	protected $reason;

	/**
	 * PlayerKickEvent constructor.
	 *
	 * @param TextContainer|string $quitMessage
	 */
	public function __construct(Player $player, string $reason, $quitMessage)
	{
		$this->player = $player;
		$this->quitMessage = $quitMessage;
		$this->reason = $reason;
	}

	public function setReason(string $reason) : void
	{
		$this->reason = $reason;
	}

	public function getReason() : string
	{
		return $this->reason;
	}

	/**
	 * @param TextContainer|string $quitMessage
	 */
	public function setQuitMessage($quitMessage) : void
	{
		$this->quitMessage = $quitMessage;
	}

	/**
	 * @return TextContainer|string
	 */
	public function getQuitMessage()
	{
		return $this->quitMessage;
	}
}
