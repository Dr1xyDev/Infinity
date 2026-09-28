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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandPermissions;

final class AdventureSettingsData
{
	/**
	 * This constant is used to identify flags that should be set on the second field. In a sensible world, these
	 * flags would all be set on the same packet field, but as of MCPE 1.2, the new abilities flags have for some
	 * reason been assigned a separate field.
	 */
	public const BITFLAG_SECOND_SET = 1 << 16;

	public const WORLD_IMMUTABLE = 0x01;
	public const NO_PVP = 0x02;

	public const AUTO_JUMP = 0x20;
	public const ALLOW_FLIGHT = 0x40;
	public const NO_CLIP = 0x80;
	public const WORLD_BUILDER = 0x100;
	public const FLYING = 0x200;
	public const MUTED = 0x400;

	public const MINE = 0x01 | self::BITFLAG_SECOND_SET;
	public const DOORS_AND_SWITCHES = 0x02 | self::BITFLAG_SECOND_SET;
	public const OPEN_CONTAINERS = 0x04 | self::BITFLAG_SECOND_SET;
	public const ATTACK_PLAYERS = 0x08 | self::BITFLAG_SECOND_SET;
	public const ATTACK_MOBS = 0x10 | self::BITFLAG_SECOND_SET;
	public const OPERATOR = 0x20 | self::BITFLAG_SECOND_SET;
	public const TELEPORT = 0x80 | self::BITFLAG_SECOND_SET;
	public const BUILD = 0x100 | self::BITFLAG_SECOND_SET;
	public const DEFAULT = 0x200 | self::BITFLAG_SECOND_SET;

	public function __construct(
		private int $flags,
		private CommandPermissions $commandPermission,
		private int $flags2,
		private int $playerPermission,
		private int $customFlags,
		private int $entityUniqueId //This is a little-endian long, NOT a var-long. (WTF Mojang)
	) {
	}

	public function getFlags() : int {
		return $this->flags;
	}

	public function getCommandPermission() : CommandPermissions {
		return $this->commandPermission;
	}

	public function getFlags2() : int {
		return $this->flags2;
	}

	public function getPlayerPermission() : int {
		return $this->playerPermission;
	}

	public function getCustomFlags() : int {
		return $this->customFlags;
	}

	public function getEntityUniqueId() : int {
		return $this->entityUniqueId;
	}

	public static function decode(NetworkBinaryStream $in) : self
	{
		$flags = $in->getUnsignedVarInt();
		$commandPermission = CommandPermissions::fromPacket($in->getUnsignedVarInt());
		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_407) {
			$flags2 = $in->getUnsignedVarInt();
			$playerPermission = $in->getUnsignedVarInt();
			$customFlags = $in->getUnsignedVarInt();
			$entityUniqueId = $in->getLLong();
		}

		return new self(
			$flags,
			$commandPermission,
			$flags2 ?? 0,
			$playerPermission ?? PlayerPermissions::VISITOR,
			$customFlags ?? 0,
			$entityUniqueId ?? 0
		);
	}

	public function encode(NetworkBinaryStream $out) : void
	{
		$out->putUnsignedVarInt($this->flags);
		$out->putUnsignedVarInt($this->commandPermission->value);
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_407) {
			$out->putUnsignedVarInt($this->flags2);
			$out->putUnsignedVarInt($this->playerPermission);
			$out->putUnsignedVarInt($this->customFlags);
			$out->putLLong($this->entityUniqueId);
		}
	}

	public function getFlag(int $flag) : bool{
		return ($this->flags & $flag) !== 0;
	}

	public function setFlag(int $flag, bool $value) : void{
		if ($value) {
			$this->flags |= $flag;
		} else {
			$this->flags &= ~$flag;
		}
	}

	public function getFlag2(int $flag) : bool{
		return ($this->flags2 & $flag) !== 0;
	}

	public function setFlag2(int $flag, bool $value) : void{
		if ($value) {
			$this->flags2 |= $flag;
		} else {
			$this->flags2 &= ~$flag;
		}
	}
}
