<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

namespace pocketmine\level;

use pocketmine\block\Block;
use pocketmine\level\format\FullChunk;
use pocketmine\math\Vector3;

interface ChunkLoader{

	
	public function getLoaderId();

	
	public function isLoaderActive();

	
	public function getPosition();

	
	public function getX();

	
	public function getZ();

	
	public function getLevel();

	
	public function onChunkChanged(FullChunk $chunk);

	
	public function onChunkLoaded(FullChunk $chunk);

	
	public function onChunkUnloaded(FullChunk $chunk);

	
	public function onChunkPopulated(FullChunk $chunk);

	
	public function onBlockChanged(Vector3 $block);

}