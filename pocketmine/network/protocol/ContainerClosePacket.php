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


class ContainerClosePacket extends DataPacket{
	const NETWORK_ID = Info::CONTAINER_CLOSE_PACKET;

	public $windowid;

	public function decode(){
		$this->windowid = $this->getByte();
	}

	public function encode(){
		$this->reset();
		$this->putByte($this->windowid);
	}

}