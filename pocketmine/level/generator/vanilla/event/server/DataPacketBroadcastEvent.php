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

namespace pocketmine\level\generator\vanilla\event\server;

use pocketmine\level\generator\vanilla\event\Cancellable;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\DataPacket;
use pocketmine\Player;

class DataPacketBroadcastEvent extends ServerEvent implements Cancellable
{
	/** @var Player[] */
	private array $players;
	/** @var DataPacket[] */
	private array $packets;

	/**
	 * @param Player[]     $players
	 * @param DataPacket[] $packets
	 */
	public function __construct(array $players, array $packets)
	{
		$this->players = $players;
		$this->packets = $packets;
	}

	/**
	 * @return Player[]
	 */
	public function getPlayers() : array
	{
		return $this->players;
	}

	/**
	 * @param Player[] $players
	 */
	public function setPlayers(array $players) : void
	{
		$this->players = $players;
	}

	/**
	 * @return DataPacket[]
	 */
	public function getPackets() : array
	{
		return $this->packets;
	}

	/**
	 * @param DataPacket[] $packets
	 */
	public function setPackets(array $packets) : void
	{
		$this->packets = $packets;
	}
}
