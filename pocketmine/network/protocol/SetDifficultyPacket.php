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

class SetDifficultyPacket extends DataPacket{
	const NETWORK_ID = Info::SET_DIFFICULTY_PACKET;

	public $difficulty;

	public function decode(){
		$this->difficulty = $this->getInt();
	}

	public function encode(){
		$this->reset();
		$this->putInt($this->difficulty);
	}

}