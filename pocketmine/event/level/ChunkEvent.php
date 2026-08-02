<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

namespace pocketmine\event\level;

use pocketmine\level\format\FullChunk;

abstract class ChunkEvent extends LevelEvent{
	
	private $chunk;

	
	public function __construct(FullChunk $chunk){
		parent::__construct($chunk->getProvider()->getLevel());
		$this->chunk = $chunk;
	}

	
	public function getChunk(){
		return $this->chunk;
	}
}