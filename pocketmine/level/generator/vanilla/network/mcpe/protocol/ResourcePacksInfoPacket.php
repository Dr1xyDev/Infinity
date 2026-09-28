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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\resourcepacks\BehaviorPackInfoEntry;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\resourcepacks\ResourcePackInfoEntry;
use pocketmine\level\generator\vanilla\utils\UUID;

use function count;

class ResourcePacksInfoPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::RESOURCE_PACKS_INFO_PACKET;

	/** @var BehaviorPackInfoEntry[] */
	public array $behaviorPackEntries = [];
	/** @var ResourcePackInfoEntry[] */
	public array $resourcePackEntries = [];
	public bool $mustAccept = false; //if true, forces client to choose between accepting packs or being disconnected
	public bool $hasAddons = false;
	public bool $hasScripts = false; //if true, causes disconnect for any platform that doesn't support scripts yet
	public bool $forceServerPacks = false;
	public UUID $worldTemplateId;
	public string $worldTemplateVersion;
	public bool $forceDisableVibrantVisuals;
	/**
	 * @var string[]
	 * @phpstan-var array<string, string>
	 */
	public array $cdnUrls = [];

	/**
	 * @generate-create-func
	 * @param ResourcePackInfoEntry[] $resourcePackEntries
	 * @param BehaviorPackInfoEntry[] $behaviorPackEntries
	 * @param string[]                $cdnUrls
	 * @phpstan-param array<string, string> $cdnUrls
	 */
	public static function create(
		array $resourcePackEntries,
		array $behaviorPackEntries,
		bool $mustAccept,
		bool $hasAddons,
		bool $hasScripts,
		bool $forceServerPacks,
		array $cdnUrls,
		UUID $worldTemplateId,
		string $worldTemplateVersion,
		bool $forceDisableVibrantVisuals
	) : self {
		$result = new self();
		$result->resourcePackEntries = $resourcePackEntries;
		$result->behaviorPackEntries = $behaviorPackEntries;
		$result->mustAccept = $mustAccept;
		$result->hasAddons = $hasAddons;
		$result->hasScripts = $hasScripts;
		$result->forceServerPacks = $forceServerPacks;
		$result->cdnUrls = $cdnUrls;
		$result->worldTemplateId = $worldTemplateId;
		$result->worldTemplateVersion = $worldTemplateVersion;
		$result->forceDisableVibrantVisuals = $forceDisableVibrantVisuals;
		return $result;
	}

	protected function decodePayload() : void
	{
		$this->mustAccept = $this->getBool();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			if ($this->protocol >= ProtocolInfo::PROTOCOL_662) {
				$this->hasAddons = $this->getBool();
			}
			$this->hasScripts = $this->getBool();
			if ($this->protocol >= ProtocolInfo::PROTOCOL_448) {
				if ($this->protocol >= ProtocolInfo::PROTOCOL_766) {
					if ($this->protocol >= ProtocolInfo::PROTOCOL_818) {
						$this->forceDisableVibrantVisuals = $this->getBool();
					}
					$this->worldTemplateId = $this->getUUID();
					$this->worldTemplateVersion = $this->getString();
				}
				if ($this->protocol < ProtocolInfo::PROTOCOL_729) {
					$this->forceServerPacks = $this->getBool();
				}
			}
		}

		if ($this->protocol < ProtocolInfo::PROTOCOL_729) {
			$behaviorPackCount = $this->getLShort();
			while ($behaviorPackCount-- > 0) {
				$this->behaviorPackEntries[] = BehaviorPackInfoEntry::read($this);
			}
		}

		$resourcePackCount = $this->protocol >= ProtocolInfo::PROTOCOL_2168 ? $this->getUnsignedVarInt() : $this->getLShort();
		while ($resourcePackCount-- > 0) {
			$this->resourcePackEntries[] = ResourcePackInfoEntry::read($this);
		}

		if ($this->protocol >= ProtocolInfo::PROTOCOL_618 && $this->protocol < ProtocolInfo::PROTOCOL_748) {
			$this->cdnUrls = [];
			for ($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; $i++) {
				$packId = $this->getString();
				$cdnUrl = $this->getString();
				$this->cdnUrls[$packId] = $cdnUrl;
			}
		}
	}

	protected function encodePayload() : void
	{
		$this->putBool($this->mustAccept);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			if ($this->protocol >= ProtocolInfo::PROTOCOL_662) {
				$this->putBool($this->hasAddons);
			}
			$this->putBool($this->hasScripts);
			if ($this->protocol >= ProtocolInfo::PROTOCOL_448) {
				if ($this->protocol >= ProtocolInfo::PROTOCOL_766) {
					if ($this->protocol >= ProtocolInfo::PROTOCOL_818) {
						$this->putBool($this->forceDisableVibrantVisuals);
					}
					$this->putUUID($this->worldTemplateId);
					$this->putString($this->worldTemplateVersion);
				}
				if ($this->protocol < ProtocolInfo::PROTOCOL_729) {
					$this->putBool($this->forceServerPacks);
				}
			}
		}

		if ($this->protocol < ProtocolInfo::PROTOCOL_729) {
			$this->putLShort(count($this->behaviorPackEntries));
			foreach ($this->behaviorPackEntries as $entry) {
				$entry->write($this);
			}
		}

		if ($this->protocol >= ProtocolInfo::PROTOCOL_2168) {
			$this->putUnsignedVarInt(count($this->resourcePackEntries));
		} else {
			$this->putLShort(count($this->resourcePackEntries));
		}
		foreach ($this->resourcePackEntries as $entry) {
			$entry->write($this);
		}

		if ($this->protocol >= ProtocolInfo::PROTOCOL_618 && $this->protocol < ProtocolInfo::PROTOCOL_748) {
			$this->putUnsignedVarInt(count($this->cdnUrls));
			foreach ($this->cdnUrls as $packId => $cdnUrl) {
				$this->putString($packId);
				$this->putString($cdnUrl);
			}
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleResourcePacksInfo($this);
	}
}
