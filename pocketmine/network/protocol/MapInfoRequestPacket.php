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

class MapInfoRequestPacket extends DataPacket {

	const NETWORK_ID = Info::MAP_INFO_REQUEST_PACKET;

	/** @var int */
	public $mapId;

	public function decode(){
		$this->mapId = $this->getLong();
	}

	public function encode(){
        $this->reset();
		$this->putLong($this->mapId);
	}

}