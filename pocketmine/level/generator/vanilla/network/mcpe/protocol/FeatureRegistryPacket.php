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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\FeatureRegistryPacketEntry;

use function count;

/**
 * Syncs world generator settings from server to client, for client-sided chunk generation.
 */
class FeatureRegistryPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::FEATURE_REGISTRY_PACKET;

	/** @var FeatureRegistryPacketEntry[] */
	private array $entries = [];

	/**
	 * @generate-create-func
	 * @param FeatureRegistryPacketEntry[] $entries
	 */
	public static function create(array $entries) : self
	{
		$result = new self();
		$result->entries = $entries;
		return $result;
	}

	/** @return FeatureRegistryPacketEntry[] */
	public function getEntries() : array
	{
		return $this->entries;
	}

	protected function decodePayload() : void
	{
		for ($this->entries = [], $i = 0, $count = $this->getUnsignedVarInt(); $i < $count; $i++) {
			$this->entries[] = FeatureRegistryPacketEntry::read($this);
		}
	}

	protected function encodePayload() : void
	{
		$this->putUnsignedVarInt(count($this->entries));
		foreach ($this->entries as $entry) {
			$entry->write($this);
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleFeatureRegistry($this);
	}
}
