<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

namespace pocketmine\level\format\generic;

use pocketmine\level\format\Chunk;
use pocketmine\level\format\ChunkSection;
use pocketmine\level\format\LevelProvider;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\utils\Binary;
use pocketmine\utils\ChunkException;

abstract class BaseChunk extends BaseFullChunk implements Chunk{

	
	protected $sections = [];

	
	protected function __construct($provider, $x, $z, array $sections, array $biomeColors = [], array $heightMap = [], array $entities = [], array $tiles = []){
		$this->provider = $provider;
		$this->x = (int) $x;
		$this->z = (int) $z;
		foreach($sections as $Y => $section){
			if($section instanceof ChunkSection){
				$this->sections[$Y] = $section;
			}else{
				throw new ChunkException("Received invalid ChunkSection instance");
			}

			if($Y >= self::SECTION_COUNT){
				throw new ChunkException("Invalid amount of chunks");
			}
		}

		if(count($biomeColors) === 256){
			$this->biomeColors = $biomeColors;
		}else{
			$this->biomeColors = array_fill(0, 256, Binary::readInt("\xff\x00\x00\x00"));
		}

		if(count($heightMap) === 256){
			$this->heightMap = $heightMap;
		}else{
			$this->heightMap = array_fill(0, 256, 127);
		}

		$this->NBTtiles = $tiles;
		$this->NBTentities = $entities;
	}

	public function getFullBlock($x, $y, $z){
		return isset($this->sections[$y >> 4]) ? $this->sections[$y >> 4]->getFullBlock($x, $y & 0x0f, $z) : 0;
	}

	public function setBlock($x, $y, $z, $blockId = null, $meta = null){
		try{
			$result = $this->getOrCreateSection($y >> 4)->setBlock($x, $y & 0x0f, $z, $blockId & 0xff, $meta & 0x0f);
			$this->hasChanged = true;
			return $result;
		}catch(ChunkException $e){
			$level = $this->getProvider();
			$this->setInternalSection($Y = $y >> 4, $level::createChunkSection($Y));
			$this->hasChanged = true;
			return $this->sections[$y >> 4]->setBlock($x, $y & 0x0f, $z, $blockId & 0xff, $meta & 0x0f);
		}
	}

	
	private function getSectionOrEmpty(int $sY): \pocketmine\level\format\ChunkSection{
		return $this->sections[$sY] ?? new EmptyChunkSection($sY);
	}

	
	private function getOrCreateSection(int $sY): \pocketmine\level\format\ChunkSection{
		if(!isset($this->sections[$sY])){
			$level = $this->getProvider();
			$this->setInternalSection($sY, $level::createChunkSection($sY));
		}
		return $this->sections[$sY];
	}

	public function getBlockId($x, $y, $z){
		return $this->getSectionOrEmpty($y >> 4)->getBlockId($x, $y & 0x0f, $z);
	}

	public function setBlockId($x, $y, $z, $id){
		try{
			$this->getOrCreateSection($y >> 4)->setBlockId($x, $y & 0x0f, $z, $id);
			$this->hasChanged = true;
		}catch(ChunkException $e){
			$level = $this->getProvider();
			$this->setInternalSection($Y = $y >> 4, $level::createChunkSection($Y));
			$this->sections[$y >> 4]->setBlockId($x, $y & 0x0f, $z, $id);
			$this->hasChanged = true;
		}
	}

	public function getBlockData($x, $y, $z){
		return $this->getSectionOrEmpty($y >> 4)->getBlockData($x, $y & 0x0f, $z);
	}

	public function setBlockData($x, $y, $z, $data){
		try{
			$this->getOrCreateSection($y >> 4)->setBlockData($x, $y & 0x0f, $z, $data);
			$this->hasChanged = true;
		}catch(ChunkException $e){
			$level = $this->getProvider();
			$this->setInternalSection($Y = $y >> 4, $level::createChunkSection($Y));
			$this->sections[$y >> 4]->setBlockData($x, $y & 0x0f, $z, $data);
			$this->hasChanged = true;
		}
	}

	public function getBlockSkyLight($x, $y, $z){
		return $this->getSectionOrEmpty($y >> 4)->getBlockSkyLight($x, $y & 0x0f, $z);
	}

	public function setBlockSkyLight($x, $y, $z, $data){
		try{
			$this->getOrCreateSection($y >> 4)->setBlockSkyLight($x, $y & 0x0f, $z, $data);
			$this->hasChanged = true;
		}catch(ChunkException $e){
			$level = $this->getProvider();
			$this->setInternalSection($Y = $y >> 4, $level::createChunkSection($Y));
			$this->sections[$y >> 4]->setBlockSkyLight($x, $y & 0x0f, $z, $data);
			$this->hasChanged = true;
		}
	}

	public function getBlockLight($x, $y, $z){
		return $this->getSectionOrEmpty($y >> 4)->getBlockLight($x, $y & 0x0f, $z);
	}

	public function setBlockLight($x, $y, $z, $data){
		try{
			$this->getOrCreateSection($y >> 4)->setBlockLight($x, $y & 0x0f, $z, $data);
			$this->hasChanged = true;
		}catch(ChunkException $e){
			$level = $this->getProvider();
			$this->setInternalSection($Y = $y >> 4, $level::createChunkSection($Y));
			$this->sections[$y >> 4]->setBlockLight($x, $y & 0x0f, $z, $data);
			$this->hasChanged = true;
		}
	}

	public function getBlockIdColumn($x, $z){
		$column = "";
		for($y = 0; $y < Chunk::SECTION_COUNT; ++$y){
			$column .= $this->getSectionOrEmpty($y)->getBlockIdColumn($x, $z);
		}
		return $column;
	}

	public function getBlockDataColumn($x, $z){
		$column = "";
		for($y = 0; $y < Chunk::SECTION_COUNT; ++$y){
			$column .= $this->getSectionOrEmpty($y)->getBlockDataColumn($x, $z);
		}
		return $column;
	}

	public function getBlockSkyLightColumn($x, $z){
		$column = "";
		for($y = 0; $y < Chunk::SECTION_COUNT; ++$y){
			$column .= $this->getSectionOrEmpty($y)->getBlockSkyLightColumn($x, $z);
		}
		return $column;
	}

	public function getBlockLightColumn($x, $z){
		$column = "";
		for($y = 0; $y < Chunk::SECTION_COUNT; ++$y){
			$column .= $this->getSectionOrEmpty($y)->getBlockLightColumn($x, $z);
		}
		return $column;
	}

	public function isSectionEmpty($fY){
		$fY = (int) $fY;
		return !isset($this->sections[$fY]) || $this->sections[$fY] instanceof EmptyChunkSection;
	}

	public function getSection($fY){
		return $this->sections[(int) $fY] ?? new EmptyChunkSection((int) $fY);
	}

	public function setSection($fY, ChunkSection $section){
		if(substr_count($section->getIdArray(), "\x00") === 4096 and substr_count($section->getDataArray(), "\x00") === 2048){
			$this->sections[(int) $fY] = new EmptyChunkSection($fY);
		}else{
			$this->sections[(int) $fY] = $section;
		}
		$this->hasChanged = true;
	}

	private function setInternalSection($fY, ChunkSection $section){
		$this->sections[(int) $fY] = $section;
		$this->hasChanged = true;
	}

	public function load($generate = true){
		return $this->getProvider() === null ? false : $this->getProvider()->getChunk($this->getX(), $this->getZ(), true) instanceof Chunk;
	}

	public function getBlockIdArray(){
		$blocks = "";
		for($y = 0; $y < Chunk::SECTION_COUNT; ++$y){
			$blocks .= $this->getSectionOrEmpty($y)->getIdArray();
		}
		return $blocks;
	}

	public function getBlockDataArray(){
		$data = "";
		for($y = 0; $y < Chunk::SECTION_COUNT; ++$y){
			$data .= $this->getSectionOrEmpty($y)->getDataArray();
		}
		return $data;
	}

	public function getBlockSkyLightArray(){
		$skyLight = "";
		for($y = 0; $y < Chunk::SECTION_COUNT; ++$y){
			$skyLight .= $this->getSectionOrEmpty($y)->getSkyLightArray();
		}
		return $skyLight;
	}

	public function getBlockLightArray(){
		$blockLight = "";
		for($y = 0; $y < Chunk::SECTION_COUNT; ++$y){
			$blockLight .= $this->getSectionOrEmpty($y)->getLightArray();
		}
		return $blockLight;
	}

	
	public function getSections(){
		return $this->sections;
	}

}