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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\stackresponse;

use pocketmine\level\generator\vanilla\network\mcpe\NetworkBinaryStream;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;

final class ItemStackResponseSlotInfo
{
	public function __construct(
		private int $slot,
		private int $hotbarSlot,
		private int $count,
		private int $itemStackId,
		private string $customName,
		private string $filteredCustomName,
		private int $durabilityCorrection
	) {
	}

	public function getSlot() : int
	{
		return $this->slot;
	}

	public function getHotbarSlot() : int
	{
		return $this->hotbarSlot;
	}

	public function getCount() : int
	{
		return $this->count;
	}

	public function getItemStackId() : int
	{
		return $this->itemStackId;
	}

	public function getCustomName() : string
	{
		return $this->customName;
	}

	public function getFilteredCustomName() : string
	{
		return $this->filteredCustomName;
	}

	public function getDurabilityCorrection() : int
	{
		return $this->durabilityCorrection;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		$slot = $in->getByte();
		$hotbarSlot = $in->getByte();
		$count = $in->getByte();
		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_2168) {
			$itemStackId = $in->getProtocolOptional(fn() => $in->readServerItemStackId()) ?? 0;
		} else {
			$itemStackId = $in->readServerItemStackId();
		}
		$customName = $in->getString();
		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_2169) {
			$filteredCustomName = $in->getOptional(fn() => $in->getString());
		} elseif ($in->getProtocol() >= ProtocolInfo::PROTOCOL_766) {
			$filteredCustomName = $in->getString();
		}
		$durabilityCorrection = $in->getVarInt();
		return new self($slot, $hotbarSlot, $count, $itemStackId, $customName, $filteredCustomName ?? $customName, $durabilityCorrection);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$out->putByte($this->slot);
		$out->putByte($this->hotbarSlot);
		$out->putByte($this->count);
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_2168) {
			$out->putProtocolOptional($this->itemStackId !== 0 ? $this->itemStackId : null, fn(int $id) => $out->writeServerItemStackId($id));
		} else {
			$out->writeServerItemStackId($this->itemStackId);
		}
		$out->putString($this->customName);
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_2169) {
			$out->putOptional($this->filteredCustomName !== "" ? $this->filteredCustomName : null, fn(string $name) => $out->putString($name));
		} elseif ($out->getProtocol() >= ProtocolInfo::PROTOCOL_766) {
			$out->putString($this->filteredCustomName);
		}
		$out->putVarInt($this->durabilityCorrection);
	}
}
