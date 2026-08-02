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

use pocketmine\entity\Entity;
use pocketmine\tile\Tile;

interface FullChunk{

	
	public function getX();

	
	public function getZ();

	public function setX($x);

	public function setZ($z);

	
	public function getProvider();

	
	public function setProvider(LevelProvider $provider);

	
	public function getFullBlock($x, $y, $z);

	
	public function setBlock($x, $y, $z, $blockId = null, $meta = null);

	
	public function getBlockId($x, $y, $z);

	
	public function setBlockId($x, $y, $z, $id);

	
	public function getBlockData($x, $y, $z);

	
	public function setBlockData($x, $y, $z, $data);

	
	public function getBlockExtraData($x, $y, $z);

	
	public function setBlockExtraData($x, $y, $z, $data);

	
	public function getBlockSkyLight($x, $y, $z);

	
	public function setBlockSkyLight($x, $y, $z, $level);

	
	public function getBlockLight($x, $y, $z);

	
	public function setBlockLight($x, $y, $z, $level);

	
	public function getHighestBlockAt($x, $z);

	
	public function getHeightMap($x, $z);

	
	public function setHeightMap($x, $z, $value);

	public function recalculateHeightMap();

	public function populateSkyLight();

	
	public function getBiomeId($x, $z);

	
	public function setBiomeId($x, $z, $biomeId);

	
	public function getBiomeColor($x, $z);

	public function getBlockIdColumn($x, $z);

	public function getBlockDataColumn($x, $z);

	public function getBlockSkyLightColumn($x, $z);

	public function getBlockLightColumn($x, $z);

	
	public function setBiomeColor($x, $z, $R, $G, $B);

	public function isLightPopulated();

	public function setLightPopulated($value = 1);

	public function isPopulated();

	public function setPopulated($value = 1);

	public function isGenerated();

	public function setGenerated($value = 1);

	
	public function addEntity(Entity $entity);

	
	public function removeEntity(Entity $entity);

	
	public function addTile(Tile $tile);

	
	public function removeTile(Tile $tile);

	
	public function getEntities();

	
	public function getTiles();

	
	public function getTile($x, $y, $z);

	
	public function isLoaded();

	
	public function load($generate = true);

	
	public function unload($save = true, $safe = true);

	public function initChunk();

	
	public function getBiomeIdArray();

	
	public function getBiomeColorArray();

	
	public function getHeightMapArray();

	public function getBlockIdArray();

	public function getBlockDataArray();

	public function getBlockExtraDataArray();

	public function getBlockSkyLightArray();

	public function getBlockLightArray();

	public function toBinary();

	public function toFastBinary();

	
	public function hasChanged();

	
	public function setChanged($changed = true);

	
	public static function fromBinary($data, LevelProvider $provider = null);

	
	public static function fromFastBinary($data, LevelProvider $provider = null);

	
	public static function getEmptyChunk($chunkX, $chunkZ, LevelProvider $provider = null);

}