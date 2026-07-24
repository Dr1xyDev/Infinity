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


class FullChunkDataPacket extends DataPacket{
	const NETWORK_ID = Info::FULL_CHUNK_DATA_PACKET;
	
	const ORDER_COLUMNS = 0;
	const ORDER_LAYERED = 1;

	public $chunkX;
	public $chunkZ;
	public $order = self::ORDER_COLUMNS;
	public $data;

	public function decode(){

	}

	public function encode(){
	    //var_dump($this->data);
		$this->reset();
        $this->putInt($this->chunkX);
        $this->putInt($this->chunkZ);
        $this->putByte($this->order);
        $this->putInt(strlen($this->data));
		$this->put($this->data);
	}

}
