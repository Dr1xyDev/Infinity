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

class DataPacketSendEvent extends ServerEvent implements Cancellable
{
	/** @var DataPacket */
	private $packet;
	/** @var Player */
	private $player;

	public function __construct(Player $player, DataPacket $packet)
	{
		$this->packet = $packet;
		$this->player = $player;
	}

	public function getPacket() : DataPacket
	{
		return $this->packet;
	}

	public function getPlayer() : Player
	{
		return $this->player;
	}
}
