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

use pocketmine\level\generator\vanilla\event\Event;
use pocketmine\level\generator\vanilla\network\NetworkInterface;
use pocketmine\Player;
use RuntimeException;

use function is_a;

/**
 * Allows the creation of players overriding the base Player class
 */
class PlayerCreationEvent extends Event
{
	public function __construct(
		protected readonly NetworkInterface $interface,
		protected string $baseClass,
		protected string $playerClass,
		protected readonly string $address,
		protected readonly int $port,
		protected readonly int $protocolVersion,
	) {
		if (!is_a($this->baseClass, Player::class, true)) {
			throw new RuntimeException("Base class $baseClass must extend " . Player::class);
		}

		if (!is_a($this->playerClass, Player::class, true)) {
			throw new RuntimeException("Class $playerClass must extend " . Player::class);
		}
	}

	public function getInterface() : NetworkInterface
	{
		return $this->interface;
	}

	public function getAddress() : string
	{
		return $this->address;
	}

	public function getPort() : int
	{
		return $this->port;
	}

	public function getBaseClass()
	{
		return $this->baseClass;
	}

	public function getProtocolVersion() : int
	{
		return $this->protocolVersion;
	}

	public function setBaseClass(string $class) : void
	{
		if (!is_a($class, $this->baseClass, true)) {
			throw new RuntimeException("Base class $class must extend " . $this->baseClass);
		}

		$this->baseClass = $class;
	}

	public function getPlayerClass() : string
	{
		return $this->playerClass;
	}

	public function setPlayerClass(string $class) : void
	{
		if (!is_a($class, $this->baseClass, true)) {
			throw new RuntimeException("Class $class must extend " . $this->baseClass);
		}

		$this->playerClass = $class;
	}
}
