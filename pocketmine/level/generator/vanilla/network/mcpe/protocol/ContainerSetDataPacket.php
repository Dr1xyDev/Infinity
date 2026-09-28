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

class ContainerSetDataPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::CONTAINER_SET_DATA_PACKET;

	public const PROPERTY_FURNACE_TICK_COUNT = 0;
	public const PROPERTY_FURNACE_LIT_TIME = 1;
	public const PROPERTY_FURNACE_LIT_DURATION = 2;
	public const PROPERTY_FURNACE_STORED_XP = 3;
	public const PROPERTY_FURNACE_FUEL_AUX = 4;

	public const PROPERTY_BREWING_STAND_BREW_TIME = 0;
	public const PROPERTY_BREWING_STAND_FUEL_AMOUNT = 1;
	public const PROPERTY_BREWING_STAND_FUEL_TOTAL = 2;

	public int $windowId;
	public int $property;
	public int $value;

	protected function decodePayload() : void
	{
		$this->windowId = $this->getByte();
		$this->property = $this->getVarInt();
		$this->value = $this->getVarInt();
	}

	protected function encodePayload() : void
	{
		$this->putByte($this->windowId);
		$this->putVarInt($this->property);
		$this->putVarInt($this->value);
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleContainerSetData($this);
	}
}
