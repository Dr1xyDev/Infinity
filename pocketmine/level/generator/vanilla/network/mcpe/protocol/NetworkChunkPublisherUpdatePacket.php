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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\ChunkPosition;

use function count;

class NetworkChunkPublisherUpdatePacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::NETWORK_CHUNK_PUBLISHER_UPDATE_PACKET;

	/** @var int */
	public $x;
	/** @var int */
	public $y;
	/** @var int */
	public $z;
	/** @var int */
	public $radius;
	/** @var ChunkPosition[] */
	public $savedChunks = [];

	public const MAX_SAVED_CHUNKS = 9216;

	protected function decodePayload() : void
	{
		$this->getSignedBlockPosition($this->x, $this->y, $this->z);
		$this->radius = $this->getUnsignedVarInt();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_544) {
			$count = $this->getLInt();
			if ($count > self::MAX_SAVED_CHUNKS) {
				throw new PacketDecodeException("Expected at most " . self::MAX_SAVED_CHUNKS . " saved chunks, got " . $count);
			}
			for ($i = 0, $this->savedChunks = []; $i < $count; $i++) {
				$this->savedChunks[] = ChunkPosition::read($this);
			}
		}
	}

	protected function encodePayload() : void
	{
		$this->putSignedBlockPosition($this->x, $this->y, $this->z);
		$this->putUnsignedVarInt($this->radius);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_544) {
			$this->putLInt(count($this->savedChunks));
			foreach ($this->savedChunks as $chunk) {
				$chunk->write($this);
			}
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleNetworkChunkPublisherUpdate($this);
	}
}
