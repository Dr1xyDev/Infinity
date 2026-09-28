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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\entity\PlayerActorProperties;

class SyncActorPropertyPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::SYNC_ACTOR_PROPERTY_PACKET;

	private CompoundTag $data;

	public static function create(CompoundTag $data) : self
	{
		$result = new self();
		$result->data = $data;
		return $result;
	}

	public static function createFromProperties(PlayerActorProperties $properties) : self
	{
		return self::create($properties->toNbt());
	}

	public function getData() : CompoundTag
	{
		return $this->data;
	}

	protected function decodePayload() : void
	{
		$this->data = $this->getNbtCompoundRoot();
	}

	protected function encodePayload() : void
	{
		$this->put((new NetworkLittleEndianNBTStream())->write($this->data));
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleSyncActorProperty($this);
	}
}
