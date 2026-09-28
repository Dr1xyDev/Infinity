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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\ItemComponentPacketEntry;

use function count;

class ItemComponentPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::ITEM_COMPONENT_PACKET;

	/**
	 * @var ItemComponentPacketEntry[]
	 * @phpstan-var list<ItemComponentPacketEntry>
	 */
	private array $entries = [];

	/**
	 * @param ItemComponentPacketEntry[] $entries
	 * @phpstan-param list<ItemComponentPacketEntry> $entries
	 */
	public static function create(array $entries) : self
	{
		$result = new self();
		$result->entries = $entries;
		return $result;
	}

	/**
	 * @return ItemComponentPacketEntry[]
	 * @phpstan-return list<ItemComponentPacketEntry>
	 */
	public function getEntries() : array
	{
		return $this->entries;
	}

	protected function decodePayload() : void
	{
		$this->entries = [];
		for ($i = 0, $len = $this->getUnsignedVarInt(); $i < $len; ++$i) {
			$name = $this->getString();
			$nbt = $this->getNbtCompoundRoot();
			$this->entries[] = new ItemComponentPacketEntry($name, $nbt);
		}
	}

	protected function encodePayload() : void
	{
		$this->putUnsignedVarInt(count($this->entries));
		foreach ($this->entries as $entry) {
			$this->putString($entry->getName());
			($this->buffer .= (new NetworkLittleEndianNBTStream())->write($entry->getComponentNbt()));
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleItemComponent($this);
	}
}
