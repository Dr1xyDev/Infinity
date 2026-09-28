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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\ChunkCacheBlob;

use function count;

class ClientCacheMissResponsePacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::CLIENT_CACHE_MISS_RESPONSE_PACKET;

	/** @var ChunkCacheBlob[] */
	private array $blobs = [];

	/**
	 * @param ChunkCacheBlob[] $blobs
	 */
	public static function create(array $blobs) : self
	{
		//type check
		(static function (ChunkCacheBlob ...$blobs) {})(...$blobs);

		$result = new self();
		$result->blobs = $blobs;
		return $result;
	}

	/**
	 * @return ChunkCacheBlob[]
	 */
	public function getBlobs() : array
	{
		return $this->blobs;
	}

	protected function decodePayload() : void
	{
		for ($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i) {
			$hash = $this->getLLong();
			$payload = $this->getString();
			$this->blobs[] = new ChunkCacheBlob($hash, $payload);
		}
	}

	protected function encodePayload() : void
	{
		$this->putUnsignedVarInt(count($this->blobs));
		foreach ($this->blobs as $blob) {
			$this->putLLong($blob->getHash());
			$this->putString($blob->getPayload());
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleClientCacheMissResponse($this);
	}
}
