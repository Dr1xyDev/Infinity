<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

namespace pocketmine\level\format;

interface ChunkSection{

	
	public function getY();

	
	public function getBlockId($x, $y, $z);

	
	public function setBlockId($x, $y, $z, $id);

	
	public function getBlockData($x, $y, $z);

	
	public function setBlockData($x, $y, $z, $data);

	
	public function getFullBlock($x, $y, $z);

	
	public function setBlock($x, $y, $z, $blockId = null, $meta = null);

	
	public function getBlockSkyLight($x, $y, $z);

	
	public function setBlockSkyLight($x, $y, $z, $level);

	
	public function getBlockLight($x, $y, $z);

	
	public function setBlockLight($x, $y, $z, $level);

	
	public function getBlockIdColumn($x, $z);

	
	public function getBlockDataColumn($x, $z);

	
	public function getBlockSkyLightColumn($x, $z);

	
	public function getBlockLightColumn($x, $z);

	public function getIdArray();

	public function getDataArray();

	public function getSkyLightArray();

	public function getLightArray();

}