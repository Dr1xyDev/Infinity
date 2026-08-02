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

class OPEN_CONNECTION_REQUEST_2 extends OfflineMessage{
	public static $ID = 0x07;

	public $clientID;
	public $serverAddress;
	public $serverPort;
	public $mtuSize;

	public function encode(){
		parent::encode();
		$this->put(RakLib::MAGIC);
		$this->putAddress($this->serverAddress, $this->serverPort, 4);
		$this->putShort($this->mtuSize);
		$this->putLong($this->clientID);
	}

	public function decode(){
		parent::decode();
		$this->offset += 16; 
		$this->getAddress($this->serverAddress, $this->serverPort);
		$this->mtuSize = $this->getShort();
		$this->clientID = $this->getLong();
	}
}
