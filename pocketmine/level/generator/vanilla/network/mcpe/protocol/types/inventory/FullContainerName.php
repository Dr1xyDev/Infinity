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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory;

use pocketmine\level\generator\vanilla\network\mcpe\NetworkBinaryStream;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;

final class FullContainerName
{
	public function __construct(
		private int $containerId,
		private ?int $dynamicId = null
	) {
	}

	public function getContainerId() : int
	{
		return $this->containerId;
	}

	public function getDynamicId() : ?int
	{
		return $this->dynamicId;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		$containerId = $in->getByte();
		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_712) {
			if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_729) {
				$dynamicId = $in->getOptional($in->getLInt(...));
			} else {
				$dynamicId = $in->getLInt();
			}
		}
		return new self($containerId, $dynamicId ?? null);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$out->putByte($this->containerId);
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_712) {
			if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_729) {
				$out->putOptional($this->dynamicId, $out->putLInt(...));
			} else {
				$out->putLInt($this->dynamicId ?? 0);
			}
		}
	}
}
