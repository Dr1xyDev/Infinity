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

class SetEntityMotionPacket extends DataPacket{
	const NETWORK_ID = Info::SET_ENTITY_MOTION_PACKET;

	
	
	public $entities = [];

	public function clean(){
		$this->entities = [];
		return parent::clean();
	}

	public function decode(){

	}

	public function encode(){
		$this->reset();
		
		foreach($this->entities as $d){
			$this->putLong($d[0]); 
			$this->putFloat($d[1]); 
			$this->putFloat($d[2]); 
			$this->putFloat($d[3]); 
		}
	}

}
