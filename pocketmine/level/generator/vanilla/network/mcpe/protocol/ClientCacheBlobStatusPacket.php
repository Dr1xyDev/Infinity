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

use function count;

class ClientCacheBlobStatusPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::CLIENT_CACHE_BLOB_STATUS_PACKET;

	/** @var int[] xxHash64 subchunk data hashes */
	public array $hitHashes = [];
	/** @var int[] xxHash64 subchunk data hashes */
	public array $missHashes = [];

	/**
	 * @param int[] $hitHashes
	 * @param int[] $missHashes
	 */
	public static function create(array $hitHashes, array $missHashes) : self
	{
		//type checks
		(static function (int ...$hashes) {})(...$hitHashes);
		(static function (int ...$hashes) {})(...$missHashes);

		$result = new self();
		$result->hitHashes = $hitHashes;
		$result->missHashes = $missHashes;
		return $result;
	}

	/**
	 * @return int[]
	 */
	public function getHitHashes() : array
	{
		return $this->hitHashes;
	}

	/**
	 * @return int[]
	 */
	public function getMissHashes() : array
	{
		return $this->missHashes;
	}

	protected function decodePayload() : void
	{
		$hitCount = $this->getUnsignedVarInt();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_1001) {
			for ($i = 0; $i < $hitCount; ++$i) {
				$this->hitHashes[] = $this->getLLong();
			}
			$missCount = $this->getUnsignedVarInt();
		} else {
			$missCount = $this->getUnsignedVarInt();
			for ($i = 0; $i < $hitCount; ++$i) {
				$this->hitHashes[] = $this->getLLong();
			}
		}

		for ($i = 0; $i < $missCount; ++$i) {
			$this->missHashes[] = $this->getLLong();
		}
	}

	protected function encodePayload() : void
	{
		$this->putUnsignedVarInt(count($this->hitHashes));
		if ($this->protocol >= ProtocolInfo::PROTOCOL_1001) {
			foreach ($this->hitHashes as $hash) {
				$this->putLLong($hash);
			}
			$this->putUnsignedVarInt(count($this->missHashes));
		} else {
			$this->putUnsignedVarInt(count($this->missHashes));
			foreach ($this->hitHashes as $hash) {
				$this->putLLong($hash);
			}
		}

		foreach ($this->missHashes as $hash) {
			$this->putLLong($hash);
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleClientCacheBlobStatus($this);
	}
}
