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

#include <rules/DataPacket.h>

use pocketmine\level\generator\vanilla\network\mcpe\NetworkSession;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\UpdateSubChunkBlocksPacketEntry;

use function count;

class UpdateSubChunkBlocksPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::UPDATE_SUB_CHUNK_BLOCKS_PACKET;

	private int $subChunkX = 0;
	private int $subChunkY = 0;
	private int $subChunkZ = 0;

	/** @var UpdateSubChunkBlocksPacketEntry[] */
	private array $layer0Updates = [];
	/** @var UpdateSubChunkBlocksPacketEntry[] */
	private array $layer1Updates = [];

	/**
	 * @param UpdateSubChunkBlocksPacketEntry[] $layer0
	 * @param UpdateSubChunkBlocksPacketEntry[] $layer1
	 */
	public static function create(int $subChunkX, int $subChunkY, int $subChunkZ, array $layer0, array $layer1) : self
	{
		$result = new self();
		$result->subChunkX = $subChunkX;
		$result->subChunkY = $subChunkY;
		$result->subChunkZ = $subChunkZ;
		$result->layer0Updates = $layer0;
		$result->layer1Updates = $layer1;
		return $result;
	}

	public function getSubChunkX() : int
	{
		return $this->subChunkX;
	}

	public function getSubChunkY() : int
	{
		return $this->subChunkY;
	}

	public function getSubChunkZ() : int
	{
		return $this->subChunkZ;
	}

	/** @return UpdateSubChunkBlocksPacketEntry[] */
	public function getLayer0Updates() : array
	{
		return $this->layer0Updates;
	}

	/** @return UpdateSubChunkBlocksPacketEntry[] */
	public function getLayer1Updates() : array
	{
		return $this->layer1Updates;
	}

	protected function decodePayload() : void
	{
		$this->subChunkX = $this->subChunkY = $this->subChunkZ = 0;
		$this->getBlockPosition($this->subChunkX, $this->subChunkY, $this->subChunkZ);
		$this->layer0Updates = [];
		for ($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i) {
			$this->layer0Updates[] = UpdateSubChunkBlocksPacketEntry::read($this);
		}
		for ($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i) {
			$this->layer1Updates[] = UpdateSubChunkBlocksPacketEntry::read($this);
		}
	}

	protected function encodePayload() : void
	{
		$this->putBlockPosition($this->subChunkX, $this->subChunkY, $this->subChunkZ);
		$this->putUnsignedVarInt(count($this->layer0Updates));
		foreach ($this->layer0Updates as $update) {
			$update->write($this);
		}
		$this->putUnsignedVarInt(count($this->layer1Updates));
		foreach ($this->layer1Updates as $update) {
			$update->write($this);
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleUpdateSubChunkBlocks($this);
	}
}
