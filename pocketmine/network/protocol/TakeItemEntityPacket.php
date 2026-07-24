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


class TakeItemEntityPacket extends DataPacket{
	const NETWORK_ID = Info::TAKE_ITEM_ENTITY_PACKET;

	public $target;
	public $eid;

	public function decode(){

	}

	public function encode(){
		$this->reset();
		$this->putLong($this->target);
		$this->putLong($this->eid);
	}

}
