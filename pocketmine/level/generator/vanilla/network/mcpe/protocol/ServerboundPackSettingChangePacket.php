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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\BoolPackSetting;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\FloatPackSetting;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\PackSetting;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\PackSettingType;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\StringPackSetting;
use pocketmine\level\generator\vanilla\utils\UUID;

class ServerboundPackSettingChangePacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::SERVERBOUND_PACK_SETTING_CHANGE_PACKET;

	private UUID $packId;
	private PackSetting $packSetting;

	/**
	 * @generate-create-func
	 */
	public static function create(UUID $packId, PackSetting $packSetting) : self{
		$result = new self();
		$result->packId = $packId;
		$result->packSetting = $packSetting;
		return $result;
	}

	public function getPackId() : UUID{ return $this->packId; }

	public function getPackSetting() : PackSetting{ return $this->packSetting; }

	protected function decodePayload() : void
	{
		$this->packId = $this->getUUID();

		$name = $this->getString();
		$typeId = PackSettingType::fromPacket($this->getUnsignedVarInt());
		$this->packSetting = match($typeId){
			PackSettingType::FLOAT => FloatPackSetting::read($this, $name),
			PackSettingType::BOOL => BoolPackSetting::read($this, $name),
			PackSettingType::STRING => StringPackSetting::read($this, $name),
		};
	}

	protected function encodePayload() : void
	{
		$this->putUUID($this->packId);
		$this->putString($this->packSetting->getName());
		$this->putUnsignedVarInt($this->packSetting->getTypeId()->value);
		$this->packSetting->write($this);
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleAddBehaviorTree($this);
	}
}
