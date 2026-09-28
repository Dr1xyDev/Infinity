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

use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\network\mcpe\NetworkSession;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\AbilitiesData;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\AdventureSettingsData;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandPermissions;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\DeviceOS;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\entity\PropertySyncData;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\EntityLink;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\GameMode;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\level\generator\vanilla\utils\UUID;

use function count;

class AddPlayerPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::ADD_PLAYER_PACKET;

	public UUID $uuid;
	public string $username;
	public ?int $entityUniqueId = null; //TODO
	public int $entityRuntimeId;
	public string $platformChatId = "";
	public Vector3 $position;
	public ?Vector3 $motion;
	public float $pitch = 0.0;
	public float $yaw = 0.0;
	public ?float $headYaw = 0.0;
	public Item|ItemStackWrapper $item;
	public int $gameMode = GameMode::SURVIVAL;
	public array $metadata = [];
	public ?PropertySyncData $syncedProperties = null;
	public AbilitiesData $abilitiesData;
	public ?AdventureSettingsData $adventureSettingsData = null;

	/** @var EntityLink[] */
	public array $links = [];

	public string $deviceId = ""; //TODO: fill player's device ID (???)
	public int $buildPlatform = DeviceOS::UNKNOWN;

	/**
	 * @generate-create-func
	 */
	public static function create(
		UUID $uuid,
		string $username,
		?int $entityUniqueId,
		int $entityRuntimeId,
		string $platformChatId,
		Vector3 $position,
		?Vector3 $motion,
		float $pitch,
		float $yaw,
		float $headYaw,
		Item|ItemStackWrapper $item,
		int $gameMode,
		array $metadata,
		PropertySyncData $syncedProperties,
		AbilitiesData $abilitiesData,
		AdventureSettingsData $adventureSettingsData,
		array $links,
		string $deviceId,
		int $buildPlatform
	) : self
	{
		$result = new self();
		$result->uuid = $uuid;
		$result->username = $username;
		$result->entityUniqueId = $entityUniqueId;
		$result->entityRuntimeId = $entityRuntimeId;
		$result->platformChatId = $platformChatId;
		$result->position = $position;
		$result->motion = $motion;
		$result->pitch = $pitch;
		$result->yaw = $yaw;
		$result->headYaw = $headYaw;
		$result->item = $item;
		$result->gameMode = $gameMode;
		$result->metadata = $metadata;
		$result->syncedProperties = $syncedProperties;
		$result->abilitiesData = $abilitiesData;
		$result->adventureSettingsData = $adventureSettingsData;
		$result->links = $links;
		$result->deviceId = $deviceId;
		$result->buildPlatform = $buildPlatform;
		return $result;
	}

	protected function decodePayload() : void
	{
		$this->uuid = $this->getUUID();
		$this->username = $this->getString();
		if ($this->protocol < ProtocolInfo::PROTOCOL_534) {
			$this->entityUniqueId = $this->getEntityUniqueId();
		}
		$this->entityRuntimeId = $this->getEntityRuntimeId();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			$this->platformChatId = $this->getString();
		}
		$this->position = $this->getVector3();
		$this->motion = $this->getVector3();
		$this->pitch = $this->getLFloat();
		$this->yaw = $this->getLFloat();
		$this->headYaw = $this->getLFloat();
		$this->item = $this->getItemStackWrapper();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_503) {
			$this->gameMode = $this->getVarInt();
		}
		$this->metadata = $this->getEntityMetadata();

		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			if ($this->protocol >= ProtocolInfo::PROTOCOL_534) {
				if ($this->protocol >= ProtocolInfo::PROTOCOL_557) {
					$this->syncedProperties = PropertySyncData::read($this);
				}
				$this->abilitiesData = AbilitiesData::decode($this);
			} else {
				$this->adventureSettingsData = AdventureSettingsData::decode($this);
			}

			$linkCount = $this->getUnsignedVarInt();
			for ($i = 0; $i < $linkCount; ++$i) {
				$this->links[$i] = $this->getEntityLink();
			}

			$this->deviceId = $this->getString();
			$this->buildPlatform = $this->getLInt();
		}
	}

	protected function encodePayload() : void
	{
		$this->putUUID($this->uuid);
		$this->putString($this->username);
		if ($this->protocol < ProtocolInfo::PROTOCOL_534) {
			$this->putEntityUniqueId($this->entityUniqueId ?? $this->entityRuntimeId);
		}
		$this->putEntityRuntimeId($this->entityRuntimeId);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			$this->putString($this->platformChatId);
		}
		$this->putVector3($this->position);
		$this->putVector3Nullable($this->motion);
		$this->putLFloat($this->pitch);
		$this->putLFloat($this->yaw);
		$this->putLFloat($this->headYaw ?? $this->yaw);
		$this->putItemStackWrapper($this->item);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_503) {
			$this->putVarInt($this->gameMode);
		}
		$this->putEntityMetadata($this->metadata);

		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			if ($this->protocol >= ProtocolInfo::PROTOCOL_534) {
				if ($this->protocol >= ProtocolInfo::PROTOCOL_557) {
					if ($this->syncedProperties === null) {
						$this->syncedProperties = new PropertySyncData([], []);
					}
					$this->syncedProperties->write($this);
				}
				$this->abilitiesData->encode($this);
			} else {
				if ($this->adventureSettingsData === null) {
					$this->adventureSettingsData = new AdventureSettingsData(0, CommandPermissions::NORMAL, 0, PlayerPermissions::VISITOR, 0, 0);
				}

				$this->adventureSettingsData->encode($this);
			}

			$this->putUnsignedVarInt(count($this->links));
			foreach ($this->links as $link) {
				$this->putEntityLink($link);
			}

			$this->putString($this->deviceId);
			$this->putLInt($this->protocol >= ProtocolInfo::PROTOCOL_2168 && $this->buildPlatform < 0 ? DeviceOS::ANDROID : $this->buildPlatform); //1.26.40+ clients disconnect on unknown platforms
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleAddPlayer($this);
	}
}
