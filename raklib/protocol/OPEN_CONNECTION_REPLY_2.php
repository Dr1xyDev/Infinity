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

class OPEN_CONNECTION_REPLY_2 extends OfflineMessage{
	public static $ID = 0x08;

	public $serverID;
	public $clientAddress;
	public $clientPort;
	public $mtuSize;

	public function encode(){
		parent::encode();
		$this->put(RakLib::MAGIC);
		$this->putLong($this->serverID);
		$this->putAddress($this->clientAddress, $this->clientPort, 4);
		$this->putShort($this->mtuSize);
		$this->putByte(0); 
	}

	public function decode(){
		parent::decode();
		$this->offset += 16; 
		$this->serverID = $this->getLong();
		$this->getAddress($this->clientAddress, $this->clientPort);
		$this->mtuSize = $this->getShort();
		
	}
}
