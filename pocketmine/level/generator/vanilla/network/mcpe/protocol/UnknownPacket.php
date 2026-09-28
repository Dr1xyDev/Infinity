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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol;

use pocketmine\level\generator\vanilla\network\mcpe\NetworkSession;

use function ord;
use function strlen;

class UnknownPacket extends DataPacket
{
	public const NETWORK_ID = -1; //Invalid, do not try to write this

	public string $payload;

	public function pid() : int
	{
		if (strlen($this->payload ?? "") > 0) {
			return ord($this->payload[0]);
		}
		return self::NETWORK_ID;
	}

	public function getName() : string
	{
		return "unknown packet";
	}

	public function decode() : void
	{
		$this->payload = $this->getRemaining();
	}

	public function encode() : void
	{
		//Do not reset the buffer, this class does not have a valid NETWORK_ID constant.
		$this->put($this->payload);
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return false;
	}
}
