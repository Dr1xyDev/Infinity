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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\UpdateBlockPacket;

final class UpdateSubChunkBlocksPacketEntry
{
	private int $x;
	private int $y;
	private int $z;
	private int $blockRuntimeId;

	private int $flags;

	//These two fields are useless 99.9% of the time; they are here to allow this packet to provide UpdateBlockSyncedPacket functionality.
	private int $syncedUpdateEntityUniqueId;
	private int $syncedUpdateType;

	public function __construct(int $x, int $y, int $z, int $blockRuntimeId, int $flags, int $syncedUpdateEntityUniqueId, int $syncedUpdateType)
	{
		$this->x = $x;
		$this->y = $y;
		$this->z = $z;
		$this->blockRuntimeId = $blockRuntimeId;
		$this->flags = $flags;
		$this->syncedUpdateEntityUniqueId = $syncedUpdateEntityUniqueId;
		$this->syncedUpdateType = $syncedUpdateType;
	}

	public static function simple(int $x, int $y, int $z, int $blockRuntimeId) : self
	{
		return new self($x, $y, $z, $blockRuntimeId, UpdateBlockPacket::FLAG_NETWORK, 0, 0);
	}

	public function getX() : int
	{
		return $this->x;
	}

	public function getY() : int
	{
		return $this->y;
	}

	public function getZ() : int
	{
		return $this->z;
	}

	public function getBlockRuntimeId() : int
	{
		return $this->blockRuntimeId;
	}

	public function getFlags() : int
	{
		return $this->flags;
	}

	public function getSyncedUpdateEntityUniqueId() : int
	{
		return $this->syncedUpdateEntityUniqueId;
	}

	public function getSyncedUpdateType() : int
	{
		return $this->syncedUpdateType;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		$x = $y = $z = 0;
		$in->getBlockPosition($x, $y, $z);
		$blockRuntimeId = $in->getUnsignedVarInt();
		$updateFlags = $in->getUnsignedVarInt();
		$syncedUpdateEntityUniqueId = $in->getUnsignedVarLong(); //this can't use the standard method because it's unsigned as opposed to the usual signed... !!!!!!
		$syncedUpdateType = $in->getUnsignedVarInt(); //this isn't even consistent with UpdateBlockSyncedPacket?!

		return new self($x, $y, $z, $blockRuntimeId, $updateFlags, $syncedUpdateEntityUniqueId, $syncedUpdateType);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$out->putBlockPosition($this->x, $this->y, $this->z);
		$out->putUnsignedVarInt($this->blockRuntimeId);
		$out->putUnsignedVarInt($this->flags);
		$out->putUnsignedVarLong($this->syncedUpdateEntityUniqueId);
		$out->putUnsignedVarInt($this->syncedUpdateType);
	}
}
