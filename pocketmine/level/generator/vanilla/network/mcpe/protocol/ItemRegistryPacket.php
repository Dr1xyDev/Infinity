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

use pocketmine\level\generator\vanilla\nbt\NetworkLittleEndianNBTStream;
use pocketmine\level\generator\vanilla\network\mcpe\NetworkSession;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\ItemTypeEntry;

use function count;

class ItemRegistryPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::ITEM_REGISTRY_PACKET;

	/**
	 * @var ItemTypeEntry[]
	 * @phpstan-var list<ItemTypeEntry>
	 */
	private array $entries = [];

	/**
	 * @generate-create-func
	 * @param ItemTypeEntry[] $entries
	 * @phpstan-param list<ItemTypeEntry> $entries
	 */
	public static function create(array $entries) : self
	{
		$result = new self();
		$result->entries = $entries;
		return $result;
	}

	/**
	 * @return ItemTypeEntry[]
	 * @phpstan-return list<ItemTypeEntry>
	 */
	public function getEntries() : array
	{
		return $this->entries;
	}

	protected function decodePayload() : void
	{
		$this->entries = [];
		for ($i = 0, $len = $this->getUnsignedVarInt(); $i < $len; ++$i) {
			$stringId = $this->getString();
			$numericId = $this->getSignedLShort();
			$isComponentBased = $this->getBool();
			$version = $this->getVarInt();
			$nbt = $this->getNbtCompoundRoot();
			$this->entries[] = new ItemTypeEntry($stringId, $numericId, $isComponentBased, $version, $nbt);
		}
	}

	protected function encodePayload() : void
	{
		$this->putUnsignedVarInt(count($this->entries));
		foreach ($this->entries as $entry) {
			$this->putString($entry->getStringId());
			$this->putLShort($entry->getNumericId());
			$this->putBool($entry->isComponentBased());
			$this->putVarInt($entry->getVersion());
			$this->put((new NetworkLittleEndianNBTStream())->write($entry->getComponentNbt()));
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleItemRegistry($this);
	}
}
