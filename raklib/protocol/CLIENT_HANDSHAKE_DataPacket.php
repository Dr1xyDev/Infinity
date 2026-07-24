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

class CLIENT_HANDSHAKE_DataPacket extends Packet
{
	public static $ID = 0x13;

	public $address;
	public $port;

	public $systemAddresses = [];

	public $sendPing;
	public $sendPong;

	public function encode()
	{}

	public function decode()
	{
		parent::decode();
		$this->getAddress($this->address, $this->port);
		for ($i = 0; $i < 10; ++$i) {
			$this->getAddress($addr, $port, $version);
			$this->systemAddresses[$i] = [$addr,
				$port,
				$version];
		}

		$this->sendPing = $this->getLong();
		$this->sendPong = $this->getLong();
	}
}