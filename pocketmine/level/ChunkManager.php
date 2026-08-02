<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

declare(strict_types=1);

namespace pocketmine\level;

use pocketmine\level\format\FullChunk;

interface ChunkManager{
	
	public function getBlockIdAt(int $x, int $y, int $z): int;

	
	public function setBlockIdAt(int $x, int $y, int $z, int $id);

	
	public function getBlockDataAt(int $x, int $y, int $z) : int;

	
	public function setBlockDataAt(int $x, int $y, int $z, int $data);

	
	public function getBlockLightAt(int $x, int $y, int $z) : int;

	
	public function updateBlockLight(int $x, int $y, int $z);

	
	public function setBlockLightAt(int $x, int $y, int $z, int $level);

	
	public function getChunk(int $chunkX, int $chunkZ);

	
	public function setChunk(int $chunkX, int $chunkZ, FullChunk $chunk = null);

	
	public function getSeed();
}