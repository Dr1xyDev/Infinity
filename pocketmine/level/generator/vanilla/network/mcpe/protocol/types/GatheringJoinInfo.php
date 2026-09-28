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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol\types;

use pocketmine\level\generator\vanilla\network\mcpe\NetworkBinaryStream;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\level\generator\vanilla\utils\UUID;
use function preg_match;

/**
 * Wire formats:
 *  - < 1.26.20: experienceId, experienceName, experienceWorldId, experienceWorldName, creatorId, storeId (all strings)
 *  - 1.26.20+: the IDs became UUIDs, storeId was replaced by targetId/scenarioId/serverId
 *  - 1.26.40+: experienceWorldId, experienceWorldName, targetId, scenarioId and serverId are optionals
 *
 * @see ServerJoinInformation
 * @see \pocketmine\level\generator\vanilla\network\mcpe\protocol\TransferPacket
 */
final class GatheringJoinInfo{

	/**
	 * @param string $storeName             unused, kept for API compatibility (see StoreEntryPointInfo)
	 * @param bool   $presenceConfiguration unused, kept for API compatibility (see PresenceInfo)
	 */
	public function __construct(
		private string $experienceId,
		private string $experienceName,
		private string $experienceWorldId,
		private string $experienceWorldName,
		private string $creatorId,
		private ?UUID $targetId = null,
		private string $scenarioId = "",
		private string $serverId = "",
		private string $storeId = "",
		private string $storeName = "",
		private bool $presenceConfiguration = false
	){}

	public function getExperienceId() : string{ return $this->experienceId; }

	public function getExperienceName() : string{ return $this->experienceName; }

	public function getExperienceWorldId() : string{ return $this->experienceWorldId; }

	public function getExperienceWorldName() : string{ return $this->experienceWorldName; }

	public function getCreatorId() : string{ return $this->creatorId; }

	public function getTargetId() : ?UUID{ return $this->targetId; }

	public function getScenarioId() : string{ return $this->scenarioId; }

	public function getServerId() : string{ return $this->serverId; }

	public function getStoreId() : string{ return $this->storeId; }

	public function getStoreName() : string{ return $this->storeName; }

	public function isPresenceConfiguration() : bool{ return $this->presenceConfiguration; }

	private static function toUuid(string $id) : UUID{
		if (preg_match('/^[0-9a-f]{8}-?[0-9a-f]{4}-?[0-9a-f]{4}-?[0-9a-f]{4}-?[0-9a-f]{12}$/i', $id) === 1) {
			return UUID::fromString($id);
		}
		return UUID::fromData($id);
	}

	public static function read(NetworkBinaryStream $in) : self{
		$protocol = $in->getProtocol();
		if ($protocol < ProtocolInfo::PROTOCOL_975) {
			return new self(
				$in->getString(),
				$in->getString(),
				$in->getString(),
				$in->getString(),
				$in->getString(),
				storeId: $in->getString()
			);
		}

		$experienceId = $in->getUUID()->toString();
		$experienceName = $in->getString();
		if ($protocol >= ProtocolInfo::PROTOCOL_2168) {
			$experienceWorldId = $in->getOptional($in->getUUID(...))?->toString() ?? "";
			$experienceWorldName = $in->getOptional($in->getString(...)) ?? "";
			$creatorId = $in->getString();
			$targetId = $in->getOptional($in->getUUID(...));
			$scenarioId = $in->getOptional($in->getString(...)) ?? "";
			$serverId = $in->getOptional($in->getString(...)) ?? "";
		} else {
			$experienceWorldId = $in->getUUID()->toString();
			$experienceWorldName = $in->getString();
			$creatorId = $in->getString();
			$targetId = $in->getUUID();
			$scenarioId = $in->getString();
			$serverId = $in->getString();
		}

		return new self($experienceId, $experienceName, $experienceWorldId, $experienceWorldName, $creatorId, $targetId, $scenarioId, $serverId);
	}

	public function write(NetworkBinaryStream $out) : void{
		$protocol = $out->getProtocol();
		if ($protocol < ProtocolInfo::PROTOCOL_975) {
			$out->putString($this->experienceId);
			$out->putString($this->experienceName);
			$out->putString($this->experienceWorldId);
			$out->putString($this->experienceWorldName);
			$out->putString($this->creatorId);
			$out->putString($this->storeId);
			return;
		}

		$out->putUUID(self::toUuid($this->experienceId));
		$out->putString($this->experienceName);
		if ($protocol >= ProtocolInfo::PROTOCOL_2168) {
			$out->putOptional($this->experienceWorldId !== "" ? self::toUuid($this->experienceWorldId) : null, $out->putUUID(...));
			$out->putOptional($this->experienceWorldName !== "" ? $this->experienceWorldName : null, $out->putString(...));
			$out->putString($this->creatorId);
			$out->putOptional($this->targetId, $out->putUUID(...));
			$out->putOptional($this->scenarioId !== "" ? $this->scenarioId : null, $out->putString(...));
			$out->putOptional($this->serverId !== "" ? $this->serverId : null, $out->putString(...));
		} else {
			$out->putUUID(self::toUuid($this->experienceWorldId));
			$out->putString($this->experienceWorldName);
			$out->putString($this->creatorId);
			$out->putUUID($this->targetId ?? new UUID());
			$out->putString($this->scenarioId);
			$out->putString($this->serverId);
		}
	}
}
