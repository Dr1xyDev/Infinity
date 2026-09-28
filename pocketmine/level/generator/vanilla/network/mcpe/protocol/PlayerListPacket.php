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

use pocketmine\level\generator\vanilla\entity\Skin;
use pocketmine\level\generator\vanilla\network\mcpe\NetworkSession;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\DeviceOS;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\PlayerListEntry;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\skin\SerializedSkin;
use pocketmine\level\generator\vanilla\utils\Color;

use function count;

class PlayerListPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::PLAYER_LIST_PACKET;

	public const TYPE_ADD = 0;
	public const TYPE_REMOVE = 1;

	/** @var PlayerListEntry[] */
	public $entries = [];
	/** @var int */
	public $type;

	public static function getPESkinId(Skin $skin) : string
	{
		$skinId = $skin->getSkinId();

		if (SerializedSkin::isSkinIdPE($skinId)) {
			return $skinId;
		}

		$type = match ($skin->getGeometryName()) {
			"geometry.humanoid.customSlim" => "CustomSlim",
			default => "Custom",
		};

		return "Standard_" . $type;
	}

	/** Since 1.26.40 every entry has its own type: [network type, inner type] by core type */
	private const NETWORK_TYPES_V2168 = [
		self::TYPE_ADD => [1, 0],
		self::TYPE_REMOVE => [0, 1],
	];

	protected function decodePayload() : void
	{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_2168) {
			$this->decodePayloadV2168();
			return;
		}

		$this->type = $this->getByte();
		$count = $this->getUnsignedVarInt();
		for ($i = 0; $i < $count; ++$i) {
			$entry = new PlayerListEntry();

			if ($this->type === self::TYPE_ADD) {
				$entry->uuid = $this->getUUID();
				$entry->entityUniqueId = $this->getEntityUniqueId();
				$entry->username = $this->getString();
				if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
					$entry->xboxUserId = $this->getString();
					$entry->platformChatId = $this->getString();
					$entry->buildPlatform = $this->getLInt();
					$entry->skin = $this->getSkin();
					$entry->isTeacher = $this->getBool();
					$entry->isHost = $this->getBool();
					if ($this->protocol >= ProtocolInfo::PROTOCOL_649) {
						$entry->isSubClient = $this->getBool();
						if ($this->protocol >= ProtocolInfo::PROTOCOL_800) {
							$entry->color = Color::fromARGB($this->getLInt());
						}
					}
				} else {
					$entry->skin = new Skin($this->getString(), $this->getString());
				}
			} else {
				$entry->uuid = $this->getUUID();
			}

			$this->entries[$i] = $entry;
		}
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			if ($this->type === self::TYPE_ADD) {
				for ($i = 0; $i < $count; ++$i) {
					$this->getBool();
				}
			}
		}
	}

	private function decodePayloadV2168() : void
	{
		$count = $this->getUnsignedVarInt();
		$this->type = self::TYPE_ADD;
		for ($i = 0; $i < $count; ++$i) {
			$networkType = $this->getUnsignedVarInt();
			$innerType = $this->getByte();
			$type = null;
			foreach (self::NETWORK_TYPES_V2168 as $coreType => [$expectedType, $expectedInnerType]) {
				if ($networkType === $expectedType && $innerType === $expectedInnerType) {
					$type = $coreType;
					break;
				}
			}
			if ($type === null) {
				throw new PacketDecodeException("Unknown player list entry type $networkType (inner type $innerType)");
			}
			$this->type = $type;

			$entry = new PlayerListEntry();
			$entry->uuid = $this->getUUID();
			if ($type === self::TYPE_ADD) {
				$entry->entityUniqueId = $this->getEntityUniqueId();
				$entry->username = $this->getString();
				$entry->xboxUserId = $this->getString();
				$entry->platformChatId = $this->getString();
				$entry->buildPlatform = $this->getLInt();
				$entry->skin = $this->getSkin();
				$entry->isTeacher = $this->getBool();
				$entry->isHost = $this->getBool();
				$entry->isSubClient = $this->getBool();
				$entry->color = Color::fromARGB($this->getLInt());
			}
			$this->entries[$i] = $entry;
		}
	}

	private function encodePayloadV2168() : void
	{
		[$networkType, $innerType] = self::NETWORK_TYPES_V2168[$this->type];
		$this->putUnsignedVarInt(count($this->entries));
		foreach ($this->entries as $entry) {
			$this->putUnsignedVarInt($networkType);
			$this->putByte($innerType);
			$this->putUUID($entry->uuid);
			if ($this->type === self::TYPE_ADD) {
				$this->putEntityUniqueId($entry->entityUniqueId);
				$this->putString($entry->username);
				$this->putString($entry->xboxUserId);
				$this->putString($entry->platformChatId);
				$this->putLInt($entry->buildPlatform < 0 ? DeviceOS::ANDROID : $entry->buildPlatform); //1.26.40+ clients disconnect on unknown platforms
				$this->putSkin($entry->skin);
				$this->putBool($entry->isTeacher);
				$this->putBool($entry->isHost);
				$this->putBool($entry->isSubClient);
				$this->putLInt(($entry->color ?? new Color(255, 255, 255))->toARGB());
			}
		}
	}

	protected function encodePayload() : void
	{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_2168) {
			$this->encodePayloadV2168();
			return;
		}

		$this->putByte($this->type);
		$this->putUnsignedVarInt(count($this->entries));
		foreach ($this->entries as $entry) {
			if ($this->type === self::TYPE_ADD) {
				$this->putUUID($entry->uuid);
				$this->putEntityUniqueId($entry->entityUniqueId);
				$this->putString($entry->username);
				if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
					$this->putString($entry->xboxUserId);
					$this->putString($entry->platformChatId);
					$this->putLInt($entry->buildPlatform);
					$this->putSkin($entry->skin);
					$this->putBool($entry->isTeacher);
					$this->putBool($entry->isHost);
					if ($this->protocol >= ProtocolInfo::PROTOCOL_649) {
						$this->putBool($entry->isSubClient);
						if ($this->protocol >= ProtocolInfo::PROTOCOL_800) {
							$this->putLInt(($entry->color ?? new Color(255, 255, 255))->toARGB());
						}
					}
				} else {
					$this->putString(self::getPESkinId($entry->skin));
					$this->putString($entry->skin->getClientFriendlySkinData($this->protocol));
				}
			} else {
				$this->putUUID($entry->uuid);
			}
		}
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			if ($this->type === self::TYPE_ADD) {
				foreach ($this->entries as $entry) {
					$this->putBool($entry->skin->getSerializedSkin()->isTrustedSkin());
				}
			}
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handlePlayerList($this);
	}
}
