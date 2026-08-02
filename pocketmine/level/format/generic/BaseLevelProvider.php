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

use pocketmine\level\format\LevelProvider;
use pocketmine\level\generator\Generator;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\LongTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\utils\LevelException;

abstract class BaseLevelProvider implements LevelProvider{
        
        protected $level;
        
        protected $path;
        
        protected $levelData;

        public function __construct(Level $level, $path){
                $this->level = $level;
                $this->path = $path;
                if(!file_exists($this->path)){
                        mkdir($this->path, 0777, true);
                }
                $nbt = new NBT(NBT::BIG_ENDIAN);
                $raw = @file_get_contents($this->getPath() . "level.dat");
                if($raw === false or $raw === ""){
                        throw new LevelException("Missing or empty level.dat at " . $this->getPath() . "level.dat");
                }
                $decompressed = zlib_decode($raw);
                if($decompressed === false or $decompressed === ""){
                        throw new LevelException("Failed to decompress level.dat (rawLen=" . strlen($raw) . ")");
                }
                $nbt->read($decompressed);
                $levelData = $nbt->getData();
                if($levelData instanceof CompoundTag and isset($levelData->Data) and $levelData->Data instanceof CompoundTag){
                        $this->levelData = $levelData->Data;
                }else{
                        $type = is_object($levelData) ? get_class($levelData) : gettype($levelData);
                        throw new LevelException("Invalid level.dat (root=$type, rawLen=" . strlen($raw) . ", decompLen=" . strlen($decompressed) . ")");
                }

                if(!isset($this->levelData->generatorName)){
                        $this->levelData->generatorName = new StringTag("generatorName", Generator::getGenerator("DEFAULT"));
                }

                if(!isset($this->levelData->generatorOptions)){
                        $this->levelData->generatorOptions = new StringTag("generatorOptions", "");
                }
        }

        public function getPath(){
                return $this->path;
        }

        public function getServer(){
                return $this->level->getServer();
        }

        public function getLevel(){
                return $this->level;
        }

        public function getName() : string{
                return $this->levelData["LevelName"];
        }

        public function getTime(){
                return $this->levelData["Time"];
        }

        public function setTime($value){
                $this->levelData->Time = new IntTag("Time", (int) $value);
        }

        public function getSeed(){
                return $this->levelData["RandomSeed"];
        }

        public function setSeed($value){
                $this->levelData->RandomSeed = new LongTag("RandomSeed", (int) $value);
        }

        public function getSpawn(){
                return new Vector3((float) $this->levelData["SpawnX"] + 0.5, (float) $this->levelData["SpawnY"], (float) $this->levelData["SpawnZ"] + 0.5);
        }

        public function setSpawn(Vector3 $pos){
                $this->levelData->SpawnX = new IntTag("SpawnX", (int) $pos->x);
                $this->levelData->SpawnY = new IntTag("SpawnY", (int) $pos->y);
                $this->levelData->SpawnZ = new IntTag("SpawnZ", (int) $pos->z);
        }

        public function doGarbageCollection(){

        }

        
        public function getLevelData(){
                return $this->levelData;
        }

        public function saveLevelData(){
                $nbt = new NBT(NBT::BIG_ENDIAN);
                $nbt->setData(new CompoundTag("", [
                        "Data" => $this->levelData
                ]));
                $buffer = $nbt->writeCompressed();
                file_put_contents($this->getPath() . "level.dat", $buffer);
        }

}
