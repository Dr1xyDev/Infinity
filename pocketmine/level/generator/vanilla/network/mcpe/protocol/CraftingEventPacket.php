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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\level\generator\vanilla\utils\UUID;

use function count;

class CraftingEventPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::CRAFTING_EVENT_PACKET;

	public int $windowId;
	public int $type;
	public UUID $id;
	/** @var ItemStackWrapper[] */
	public array $input = [];
	/** @var ItemStackWrapper[] */
	public array $output = [];

	protected function decodePayload() : void
	{
		$this->windowId = $this->getByte();
		$this->type = $this->getVarInt();
		$this->id = $this->getUUID();

		$size = $this->getUnsignedVarInt();
		for ($i = 0; $i < $size && $i < 128; ++$i) {
			$this->input[] = $this->getItemStackWrapper();
		}

		$size = $this->getUnsignedVarInt();
		for ($i = 0; $i < $size && $i < 128; ++$i) {
			$this->output[] = $this->getItemStackWrapper();
		}
	}

	protected function encodePayload() : void
	{
		$this->putByte($this->windowId);
		$this->putVarInt($this->type);
		$this->putUUID($this->id);

		$this->putUnsignedVarInt(count($this->input));
		foreach ($this->input as $item) {
			$this->putItemStackWrapper($item);
		}

		$this->putUnsignedVarInt(count($this->output));
		foreach ($this->output as $item) {
			$this->putItemStackWrapper($item);
		}
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleCraftingEvent($this);
	}
}
