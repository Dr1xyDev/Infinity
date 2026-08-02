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

class OPEN_CONNECTION_REPLY_1 extends OfflineMessage{
	public static $ID = 0x06;

	public $serverID;
	public $mtuSize;

	public function encode(){
		parent::encode();
		$this->put(RakLib::MAGIC);
		$this->putLong($this->serverID);
		$this->putByte(0); 
		$this->putShort($this->mtuSize);
	}

	public function decode(){
		parent::decode();
		$this->offset += 16; 
		$this->serverID = $this->getLong();
		$this->getByte(); 
		$this->mtuSize = $this->getShort();
	}
}