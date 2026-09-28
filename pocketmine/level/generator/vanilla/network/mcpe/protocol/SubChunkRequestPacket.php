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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\SubChunkPosition;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\SubChunkPositionOffset;

use function count;

class SubChunkRequestPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::SUB_CHUNK_REQUEST_PACKET;

	private int $dimension;
	private SubChunkPosition $basePosition;
	/**
	 * @var SubChunkPositionOffset[]
	 * @phpstan-var list<SubChunkPositionOffset>
	 */
	private array $entries = [];

	/**
	 * @generate-create-func
	 * @param SubChunkPositionOffset[] $entries
	 * @phpstan-param list<SubChunkPositionOffset> $entries
	 */
	public static function create(int $dimension, SubChunkPosition $basePosition, array $entries) : self
	{
		$result = new self();
		$result->dimension = $dimension;
		$result->basePosition = $basePosition;
		$result->entries = $entries;
		return $result;
	}

	public function getDimension() : int
	{
		return $this->dimension;
	}

	public function getBasePosition() : SubChunkPosition
	{
		return $this->basePosition;
	}

	/**
	 * @return SubChunkPositionOffset[]
	 * @phpstan-return list<SubChunkPositionOffset>
	 */
	public function getEntries() : array
	{
		return $this->entries;
	}

	protected function decodePayload() : void
	{
		$this->dimension = $this->getVarInt();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_1001) {
			for ($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; $i++) {
				$this->entries[] = SubChunkPositionOffset::read($this);
			}

			$this->basePosition = SubChunkPosition::readFixedInts($this);
		} else {
			$this->basePosition = SubChunkPosition::readVarInts($this);

			if ($this->protocol >= ProtocolInfo::PROTOCOL_486) {
				for ($i = 0, $count = $this->getLInt(); $i < $count; $i++) {
					$this->entries[] = SubChunkPositionOffset::read($this);
				}
			}
		}
	}

	protected function encodePayload() : void
	{
		$this->putVarInt($this->dimension);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_1001) {
			$this->putUnsignedVarInt(count($this->entries));
			foreach ($this->entries as $entry) {
				$entry->write($this);
			}

			$this->basePosition->writeFixedInts($this);
		} else {
			$this->basePosition->writeVarInts($this);

			if ($this->protocol >= ProtocolInfo::PROTOCOL_486) {
				$this->putLInt(count($this->entries));
				foreach ($this->entries as $entry) {
					$entry->write($this);
				}
			}
		}
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleSubChunkRequest($this);
	}
}
