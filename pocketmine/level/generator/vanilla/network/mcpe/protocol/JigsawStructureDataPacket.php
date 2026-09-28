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

class JigsawStructureDataPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::JIGSAW_STRUCTURE_DATA_PACKET;

	private CompoundTag $nbt;

	/**
	 * @generate-create-func
	 */
	public static function create(CompoundTag $nbt) : self
	{
		$result = new self();
		$result->nbt = $nbt;
		return $result;
	}

	public function getNbt() : CompoundTag
	{
		return $this->nbt;
	}

	protected function decodePayload() : void
	{
		$this->nbt = $this->getNbtCompoundRoot();
	}

	protected function encodePayload() : void
	{
		$this->put((new NetworkLittleEndianNBTStream())->write($this->nbt));
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleJigsawStructureData($this);
	}
}
