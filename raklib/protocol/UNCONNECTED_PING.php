<?php

/*
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.0
 *               InfinityProject By @Dr1xyDev
 *   YT:         @Dr1xyDev
 *   GitHub:     github.com/Dr1xyDev/Infinity
*/

namespace raklib\protocol;

#include <rules/RakLibPacket.h>

use raklib\RakLib;

class UNCONNECTED_PING extends Packet
{
	public static $ID = 0x01;

	public $pingID;

	public function encode()
	{
		parent::encode();
		$this->putLong($this->pingID);
		$this->put(RakLib::MAGIC);
	}

	public function decode()
	{
		parent::decode();
		$this->pingID = $this->getLong();
		// magic
	}
}