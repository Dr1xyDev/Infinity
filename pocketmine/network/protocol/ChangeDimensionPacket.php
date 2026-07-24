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

class ChangeDimensionPacket extends DataPacket{
	const NETWORK_ID = Info::CHANGE_DIMENSION_PACKET;

	const DIMENSION_NORMAL = 0;
	const DIMENSION_NETHER = 1;

	public $dimension;

	public $x;
	public $y;
	public $z;

	public function decode(){

	}

	public function encode(){
		$this->reset();
		$this->putByte($this->dimension);
		$this->putFloat($this->x);
		$this->putFloat($this->y);
		$this->putFloat($this->z);
		$this->putByte(0);
	}

}