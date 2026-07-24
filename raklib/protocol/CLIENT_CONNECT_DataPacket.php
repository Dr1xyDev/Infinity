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

class CLIENT_CONNECT_DataPacket extends Packet
{
	public static $ID = 0x09;

	public $clientID;
	public $sendPing;
	public $useSecurity = false;

	public function encode()
	{
		parent::encode();
		$this->putLong($this->clientID);
		$this->putLong($this->sendPing);
		$this->putByte($this->useSecurity ? 1 : 0);
	}

	public function decode()
	{
		parent::decode();
		$this->clientID = $this->getLong();
		$this->sendPing = $this->getLong();
		$this->useSecurity = $this->getByte() > 0;
	}
}