<?php

/*
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.0
 *               InfinityProject By @Dr1xyDev
 *   YT:         @Dr1xyDev
 *   GitHub:     github.com/Dr1xyDev/Infinity
*/

namespace pocketmine\network\protocol;

#include <rules/DataPacket.h>

use pocketmine\item\Item;

class ContainerSetSlotPacket extends DataPacket{
	const NETWORK_ID = Info::CONTAINER_SET_SLOT_PACKET;

	public $windowid;
	public $slot;
	/** @var Item */
	public $item;
	public $hotbarSlot;



	public function decode(){
		$this->windowid = $this->getByte();
		$this->slot = $this->getShort();
		$this->hotbarSlot = $this->getShort();
		$this->item = $this->getSlot();
	}

	public function encode(){
		$this->reset();
		$this->putByte($this->windowid);
		$this->putShort($this->slot);
		$this->putShort($this->hotbarSlot);
		$this->putSlot($this->item);
	}

}
