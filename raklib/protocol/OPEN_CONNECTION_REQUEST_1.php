<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

namespace raklib\protocol;

use raklib\RakLib;

class OPEN_CONNECTION_REQUEST_1 extends OfflineMessage{
	public static $ID = 0x05;

	public $protocol = RakLib::PROTOCOL;
	public $mtuSize;

	public function encode(){
		parent::encode();
		$this->put(RakLib::MAGIC);
		$this->putByte($this->protocol);
		$this->buffer = str_pad($this->buffer, "\x00", $this->mtuSize);
	}

	public function decode(){
		parent::decode();
		$this->offset += 16; 
		$this->protocol = $this->getByte();
		$this->mtuSize = strlen($this->buffer);
	}
}