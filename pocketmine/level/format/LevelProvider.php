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

use pocketmine\level\Level;
use pocketmine\math\Vector3;

interface LevelProvider{

	const ORDER_YZX = 0;
	const ORDER_ZXY = 1;

	
	public function __construct(Level $level, $path);

	
	public static function getProviderName();

	
	public static function getProviderOrder();

	
	public static function usesChunkSection();

	
	public function requestChunkTask($x, $z);

	
	public function getPath();

	
	public static function isValid($path);

	
	public static function generate($path, $name, $seed, $generator, array $options = []);

	
	public function getGenerator();

	
	public function getGeneratorOptions();

	
	public function getChunk($X, $Z, $create = false);

	
	public static function createChunkSection($Y);

	public function saveChunks();

	
	public function saveChunk($X, $Z);

	public function unloadChunks();

	
	public function loadChunk($X, $Z, $create = false);

	
	public function unloadChunk($X, $Z, $safe = true);

	
	public function isChunkGenerated($X, $Z);

	
	public function isChunkPopulated($X, $Z);

	
	public function isChunkLoaded($X, $Z);

	
	public function setChunk($chunkX, $chunkZ, FullChunk $chunk);

	
	public function getName();

	
	public function getTime();

	
	public function setTime($value);

	
	public function getSeed();

	
	public function setSeed($value);

	
	public function getSpawn();

	
	public function setSpawn(Vector3 $pos);

	
	public function getLoadedChunks();

	public function doGarbageCollection();

	
	public function getLevel();

	public function close();

}