<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

namespace pocketmine\network\protocol;

class SetHealthPacket extends DataPacket{
	const NETWORK_ID = Info::SET_HEALTH_PACKET;

	public $health;

	public function decode(){
		$this->health = $this->getInt();
	}

	public function encode(){
		$this->reset();
		$this->putInt($this->health);
	}

}