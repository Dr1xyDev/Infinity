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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\CreativeGroupEntry;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\CreativeItemEntry;

use function count;

class CreativeContentPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::CREATIVE_CONTENT_PACKET;

	public const CATEGORY_CONSTRUCTION = 1;
	public const CATEGORY_NATURE = 2;
	public const CATEGORY_EQUIPMENT = 3;
	public const CATEGORY_ITEMS = 4;

	/** @var CreativeGroupEntry[] */
	private array $groups = [];
	/** @var CreativeItemEntry[] */
	private array $items = [];

	/**
	 * @generate-create-func
	 * @param CreativeGroupEntry[] $groups
	 * @param CreativeItemEntry[]  $items
	 */
	public static function create(array $groups, array $items) : self
	{
		$result = new self();
		$result->groups = $groups;
		$result->items = $items;
		return $result;
	}

	/** @return CreativeGroupEntry[] */
	public function getGroups() : array
	{
		return $this->groups;
	}

	/** @return CreativeItemEntry[] */
	public function getItems() : array
	{
		return $this->items;
	}

	protected function decodePayload() : void{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_776) {
			for ($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i) {
				$this->groups[] = CreativeGroupEntry::read($this);
			}
		}

		for ($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i) {
			$this->items[] = CreativeItemEntry::read($this);
		}
	}

	protected function encodePayload() : void
	{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_776) {
			$this->putUnsignedVarInt(count($this->groups));
			foreach ($this->groups as $entry) {
				$entry->write($this);
			}
		}

		$this->putUnsignedVarInt(count($this->items));
		foreach ($this->items as $entry) {
			$entry->write($this);
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleCreativeContent($this);
	}

}
