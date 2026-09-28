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

use pocketmine\level\generator\vanilla\nbt\NetworkLittleEndianNBTStream;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\level\generator\vanilla\network\mcpe\NetworkSession;

class UpdateBlockPropertiesPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::UPDATE_BLOCK_PROPERTIES_PACKET;

	public string $nbt;

	public static function create(CompoundTag $data) : self
	{
		$result = new self();
		$result->nbt = (new NetworkLittleEndianNBTStream())->write($data);
		return $result;
	}

	protected function decodePayload() : void
	{
		$this->nbt = $this->getRemaining();
	}

	protected function encodePayload() : void
	{
		$this->put($this->nbt);
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleUpdateBlockProperties($this);
	}
}
