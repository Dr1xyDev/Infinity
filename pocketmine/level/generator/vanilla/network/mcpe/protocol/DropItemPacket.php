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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\ItemStack;

class DropItemPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::DROP_ITEM_PACKET;

	public int $type;
	public ItemStack $item;

	protected function decodePayload() : void
	{
		$this->type = $this->getByte();
		$this->item = $this->getItemStackWithoutStackId();
	}

	protected function encodePayload() : void
	{
		$this->putByte($this->type);
		$this->putItemStackWithoutStackId($this->item);
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleDropItem($this);
	}

}
