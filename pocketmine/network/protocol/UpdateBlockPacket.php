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


class UpdateBlockPacket extends DataPacket{
	const NETWORK_ID = Info::UPDATE_BLOCK_PACKET;

	const FLAG_NONE      = 0b0000;
	const FLAG_NEIGHBORS = 0b0001;
    const FLAG_NETWORK   = 0b0010;
	const FLAG_NOGRAPHIC = 0b0100;
	const FLAG_PRIORITY  = 0b1000;

	const FLAG_ALL = (self::FLAG_NEIGHBORS | self::FLAG_NETWORK);
	const FLAG_ALL_PRIORITY = (self::FLAG_ALL | self::FLAG_PRIORITY);

	public $x;
	public $z;
	public $y;
	public $blockId;
	public $blockData;
	public $flags;

	public function decode(){

	}

	public function encode(){
		$this->reset();
		$this->putInt($this->x);
		$this->putInt($this->z);
		$this->putByte($this->y);
		$this->putByte($this->blockId);
		$this->putByte(($this->flags << 4) | $this->blockData);
	}

}