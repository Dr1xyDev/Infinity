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

class PlayerTransferEvent extends PlayerEvent implements Cancellable
{
	/** @var string */
	protected $address;
	/** @var int */
	protected $port = 19132;
	/** @var string */
	protected $message;

	public function __construct(Player $player, string $address, int $port, string $message)
	{
		$this->player = $player;
		$this->address = $address;
		$this->port = $port;
		$this->message = $message;
	}

	public function getAddress() : string
	{
		return $this->address;
	}

	public function setAddress(string $address) : void
	{
		$this->address = $address;
	}

	public function getPort() : int
	{
		return $this->port;
	}

	public function setPort(int $port) : void
	{
		$this->port = $port;
	}

	public function getMessage() : string
	{
		return $this->message;
	}

	public function setMessage(string $message) : void
	{
		$this->message = $message;
	}
}
