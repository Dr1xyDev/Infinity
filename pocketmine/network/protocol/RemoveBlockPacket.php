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


class RemoveBlockPacket extends DataPacket{
	const NETWORK_ID = Info::REMOVE_BLOCK_PACKET;

	public $eid;
	public $x;
	public $y;
	public $z;

	public function decode(){
		$this->eid = $this->getLong();
		$this->x = $this->getInt();
		$this->z = $this->getInt();
		$this->y = $this->getByte();
	}

	public function encode(){

	}

}
