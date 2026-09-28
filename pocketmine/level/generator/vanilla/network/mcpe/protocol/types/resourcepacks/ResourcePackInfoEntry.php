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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol\types\resourcepacks;

use pocketmine\level\generator\vanilla\network\mcpe\NetworkBinaryStream;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\level\generator\vanilla\utils\UUID;

class ResourcePackInfoEntry
{
	public function __construct(
		protected string $packId,
		protected string $version,
		protected int $sizeBytes,
		protected string $encryptionKey = "",
		protected string $subPackName = "",
		protected string $contentId = "",
		protected bool $hasScripts = false,
		protected bool $isAddonPack = false,
		protected bool $isRtxCapable = false,
		protected string $cdnUrl = ""
	) {
	}

	public function getPackId() : string
	{
		return $this->packId;
	}

	public function getVersion() : string
	{
		return $this->version;
	}

	public function getSizeBytes() : int
	{
		return $this->sizeBytes;
	}

	public function getEncryptionKey() : string
	{
		return $this->encryptionKey;
	}

	public function getSubPackName() : string
	{
		return $this->subPackName;
	}

	public function getContentId() : string
	{
		return $this->contentId;
	}

	public function hasScripts() : bool
	{
		return $this->hasScripts;
	}

	public function isAddonPack() : bool
	{
		return $this->isAddonPack;
	}

	public function isRtxCapable() : bool
	{
		return $this->isRtxCapable;
	}

	public function getCdnUrl() : string
	{
		return $this->cdnUrl;
	}

	public function write(NetworkBinaryStream $out) : void
	{
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_766) {
			$out->putUUID(UUID::fromString($this->packId));
		} else {
			$out->putString($this->packId);
		}
		$out->putString($this->version);
		$out->putLLong($this->sizeBytes);
		$out->putString($this->encryptionKey);
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_407) {
			$out->putString($this->subPackName);
			$out->putString($this->contentId);
			$out->putBool($this->hasScripts);
			if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_422) {
				$out->putBool($this->isAddonPack);
				if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_712) {
					$out->putBool($this->isRtxCapable);
					if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_748) {
						$out->putString($this->cdnUrl);
					}
				}
			}
		}
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_766) {
			$uuid = $in->getUUID()->toString();
		} else {
			$uuid = $in->getString();
		}
		$version = $in->getString();
		$sizeBytes = $in->getLLong();
		$encryptionKey = $in->getString();
		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_407) {
			$subPackName = $in->getString();
			$contentId = $in->getString();
			$hasScripts = $in->getBool();
			if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_422) {
				$isAddonPack = $in->getBool();
				if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_712) {
					$rtxCapable = $in->getBool();
					if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_748) {
						$cdnUrl = $in->getString();
					}
				}
			}
		}

		return new self(
			$uuid,
			$version,
			$sizeBytes,
			$encryptionKey,
			$subPackName ?? "",
			$contentId ?? $uuid,
			$hasScripts ?? false,
			$isAddonPack ?? false,
			$rtxCapable ?? false,
			$cdnUrl ?? ""
		);
	}
}
