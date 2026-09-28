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

namespace pocketmine\level\generator\vanilla\network\mcpe;

interface PacketSender
{
	/**
	 * Sends a DataPacket to the interface, returns an unique identifier for the packet if $needACK is true
	 */
	public function putBuffer(int $sessionId, string $payload, bool $needACK = false, bool $immediate = true) : ?int;

	/**
	 * Terminates the connection
	 */
	public function close(int $sessionId, string $reason = "unknown reason") : void;
}
